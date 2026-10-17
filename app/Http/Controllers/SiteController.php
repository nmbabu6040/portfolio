<?php

namespace App\Http\Controllers;

use App\Models\{Entry, Setting, Post};
use App\Support\MailConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\{Crypt, DB};
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class SiteController extends Controller
{
    private function q(string $t)
    {
        return Entry::of($t)->newQuery()->orderBy('sort')->orderBy('id');
    }
    private function v(string $view, array $d = [])
    {
        return view($view, ['s' => Setting::map(), 'p' => config('portfolio')] + $d);
    }
    private function cats()
    {
        return Entry::of('categories')->newQuery()->orderBy('sort')->orderBy('id')->get()
            ->map(function ($c) {
                $c->n = Post::where('published', 1)->where('category', $c->slug)->count();
                return $c;
            })
            ->filter(fn($c) => $c->n > 0)->values();
    }
    public function home()
    {
        return $this->v('home', [
            'tech' => $this->q('technologies')->where('visible', 1)->get(),
            'services' => $this->q('services')->get(),
            'projects' => Entry::of('projects')->newQuery()->orderBy('sort')->orderByDesc('id')->limit(3)->get(),
            'stats' => $this->q('stats')->get()
        ]);
    }
    public function about()
    {
        return $this->v('pages.about', ['why' => $this->q('reasons')->get(), 'stats' => $this->q('stats')->get()]);
    }
    public function experience()
    {
        return $this->v('pages.experience', ['exp' => $this->q('experiences')->get(), 'edu' => $this->q('educations')->get(), 'certs' => $this->q('certifications')->get(), 'ach' => $this->q('achievements')->get()]);
    }
    public function technology()
    {
        return $this->v('pages.technology', ['tech' => $this->q('technologies')->where('visible', 1)->get(), 'skills' => $this->q('skills')->get()]);
    }
    public function projects()
    {
        return $this->v('pages.projects', ['projects' => Entry::of('projects')->newQuery()->orderBy('sort')->orderByDesc('id')->paginate(9), 'cats' => $this->cats()]);
    }
    public function project(int $id)
    {
        $pr = Entry::of('projects')->newQuery()->findOrFail($id);
        return $this->v('pages.project', ['pr' => $pr, 'related' => $this->q('projects')->where('id', '!=', $id)->where('category', $pr->category)->limit(3)->get()]);
    }
    public function services()
    {
        return $this->v('pages.services', ['services' => $this->q('services')->get(), 'flow' => $this->q('workflows')->get()]);
    }
    public function service(string $slug)
    {
        $sv = Entry::of('services')->newQuery()->where('slug', $slug)->firstOrFail();
        return $this->v('pages.service', ['sv' => $sv, 'others' => $this->q('services')->where('id', '!=', $sv->id)->get()]);
    }
    public function contactPage()
    {
        return $this->v('pages.contact');
    }

    private function src(Request $r): array
    {
        $s = (array) $r->session()->get('lead_src', []);
        return ['source' => $s['label'] ?? 'Direct', 'landing' => $s['landing'] ?? null, 'campaign' => $s['campaign'] ?? null, 'referrer' => $s['referrer'] ?? null];
    }
    public function cta(Request $r)
    {
        $name = (string) $r->input('name');
        if (!auth()->check() && in_array($name, ['whatsapp', 'phone', 'email', 'quote', 'book', 'cv'], true)) {
            try {
                DB::table('cta_clicks')->insert(['name' => $name, 'path' => mb_substr('/' . ltrim((string) $r->input('path', '/'), '/'), 0, 190), 'day' => now()->toDateString(), 'created_at' => now()]);
            } catch (\Throwable $e) {
            }
        }
        return response()->noContent();
    }

    public function book()
    {
        abort_unless(!empty(Setting::map()['booking_url']), 404);
        return $this->v('pages.book');
    }
    public function privacy()
    {
        return $this->v('pages.privacy');
    }

    public function subscribe(Request $r)
    {
        $v = Validator::make($r->all(), ['email' => 'required|email|max:150', 'website' => 'max:0']);
        if ($v->fails()) return redirect(url()->previous() . '#nl')->withErrors($v)->withInput();
        $email = strtolower(trim((string) $r->input('email')));
        $row = Entry::of('subscribers')->newQuery()->where('email', $email)->first();
        if ($row) {
            if ($row->status !== 'active') $row->update(['status' => 'active']);
        } else {
            Entry::of('subscribers')->newQuery()->create(['email' => $email, 'token' => Str::random(40), 'status' => 'active', 'source' => mb_substr((string) $r->headers->get('referer'), 0, 150)]);
        }
        return redirect(url()->previous() . '#nl')->with('subscribed', 'Thanks for subscribing!');
    }
    public function unsubscribePage(string $token)
    {
        $row = Entry::of('subscribers')->newQuery()->where('token', $token)->first();
        return $this->v('pages.unsubscribe', ['row' => $row, 'done' => false]);
    }
    public function unsubscribe(string $token)
    {
        $row = Entry::of('subscribers')->newQuery()->where('token', $token)->first();
        if ($row) $row->update(['status' => 'unsubscribed']);
        return $this->v('pages.unsubscribe', ['row' => $row, 'done' => true]);
    }
    public function affiliate()
    {
        return $this->v('pages.affiliate');
    }
    public function quotePage()
    {
        return $this->v('pages.quote', ['services' => $this->q('services')->get()]);
    }
    public function quote(Request $r)
    {
        $d = $r->validate(['name' => 'required|min:2|max:100', 'email' => 'required|email|max:150', 'phone' => 'nullable|max:40', 'service' => 'required|max:150', 'budget' => 'nullable|max:100', 'deadline' => 'nullable|max:60', 'message' => 'required|min:10|max:3000', 'website' => 'max:0']);
        $src = $this->src($r);
        Entry::of('messages')->newQuery()->create([
            'name' => $d['name'],
            'email' => $d['email'],
            'phone' => $d['phone'] ?? null,
            'subject' => 'Quote request: ' . $d['service'],
            'message' => $d['message'],
            'kind' => 'quote',
            'service' => $d['service'],
            'budget' => $d['budget'] ?? null,
            'deadline' => $d['deadline'] ?? null,
        ] + $src);
        try {
            $s = Setting::map();
            MailConfig::apply($s);
            $to = $s['email'] ?? config('portfolio.email');
            Mail::raw("New quote request\n\nService: {$d['service']}\nBudget: " . ($d['budget'] ?? '-') . "\nDeadline: " . ($d['deadline'] ?? '-') . "\nCame from: {$src['source']}\n\nName: {$d['name']}\nEmail: {$d['email']}\nPhone: " . ($d['phone'] ?? '') . "\n\n{$d['message']}", fn($m) => $m->to($to)->replyTo($d['email'])->subject('Quote request: ' . $d['service']));
        } catch (\Throwable $e) {
            report($e);
        }
        return redirect('/quote')->with('sent', 'Thank you! I have received your request and will reply with details soon.');
    }
    public function blog(Request $r)
    {
        $q = mb_substr(trim((string) $r->query('q', '')), 0, 80);
        $like = '%' . addcslashes($q, '%_\\') . '%';
        $posts = Post::where('published', 1)
            ->when($q !== '', fn($x) => $x->where(fn($y) => $y->where('title', 'like', $like)->orWhere('excerpt', 'like', $like)->orWhere('body', 'like', $like)))
            ->latest()->paginate(9)->withQueryString();
        return view('blog.index', ['s' => Setting::map(), 'posts' => $posts, 'cats' => $this->cats(), 'active' => null, 'q' => $q]);
    }
    public function category(string $slug)
    {
        $cat = Entry::of('categories')->newQuery()->where('slug', $slug)->firstOrFail();
        return view('blog.index', ['s' => Setting::map(), 'posts' => Post::where('published', 1)->where('category', $slug)->latest()->paginate(9), 'cats' => $this->cats(), 'active' => $cat]);
    }
    public function post(Request $r, string $slug)
    {
        $post = Post::where('published', 1)->where('slug', $slug)->firstOrFail();
        $related = Post::where('published', 1)->where('id', '!=', $post->id)
            ->when($post->category, fn($q) => $q->orderByRaw('category = ? desc', [$post->category]))->latest()->limit(3)->get();
        $cat = $post->category ? Entry::of('categories')->newQuery()->where('slug', $post->category)->first() : null;
        try {
            $all = Entry::of('comments')->newQuery()->where('post_id', $post->id)->where('status', 'approved')->orderBy('id')->get();
            $likes = DB::table('post_likes')->where('post_id', $post->id)->count();
            $liked = DB::table('post_likes')->where('post_id', $post->id)->where('visitor', $this->visitor($r))->exists();
        } catch (\Throwable $e) {
            $all = collect();
            $likes = 0;
            $liked = false;
        }
        return view('blog.show', [
            's' => Setting::map(),
            'post' => $post,
            'related' => $related,
            'cat' => $cat,
            'comments' => $all->whereNull('parent_id')->values(),
            'replies' => $all->whereNotNull('parent_id')->groupBy('parent_id'),
            'commentCount' => $all->count(),
            'likes' => $likes,
            'liked' => $liked,
        ]);
    }
    private function visitor(Request $r): string
    {
        return hash('sha256', $r->ip() . '|' . $r->userAgent() . '|' . config('app.key'));
    }
    public function comment(Request $r, string $slug)
    {
        $post = Post::where('published', 1)->where('slug', $slug)->firstOrFail();
        $back = url('/blog/' . $post->slug) . '#comments';
        if (!$post->comments_open) return redirect($back)->with('comment_err', 'Comments are closed for this post.');
        $v = Validator::make($r->all(), ['name' => 'required|min:2|max:60', 'email' => 'required|email|max:150', 'body' => 'required|min:3|max:2000', 'parent_id' => 'nullable|integer', 'website' => 'max:0', '_t' => 'required']);
        if ($v->fails()) return redirect($back)->withErrors($v, 'comment')->withInput();
        try {
            $t = (int) Crypt::decryptString((string) $r->input('_t'));
        } catch (\Throwable $e) {
            $t = 0;
        }
        if (time() - $t < 3 || time() - $t > 86400) return redirect($back)->with('comment_err', 'Please wait a moment and try again.')->withInput();
        $q = fn() => Entry::of('comments')->newQuery();
        $ip = hash('sha256', $r->ip() . '|' . config('app.key'));
        if ($q()->where('ip_hash', $ip)->where('created_at', '>=', now()->subHour())->count() >= 5) return redirect($back)->with('comment_err', 'Too many comments from your network. Please try again later.')->withInput();
        $email = strtolower(trim((string) $r->input('email')));
        $body = trim((string) $r->input('body'));
        if ($q()->where('post_id', $post->id)->where('email', $email)->where('body', $body)->exists()) return redirect($back)->with('comment_ok', 'Thanks! Your comment is waiting for approval.');
        $parent = null;
        if ($pid = (int) $r->input('parent_id')) {
            $parent = $q()->where('id', $pid)->where('post_id', $post->id)->where('status', 'approved')->first();
            if ($parent && $parent->parent_id) $parent = $q()->find($parent->parent_id);
        }
        $c = $q()->create([
            'post_id' => $post->id,
            'parent_id' => $parent->id ?? null,
            'name' => trim((string) $r->input('name')),
            'email' => $email,
            'body' => $body,
            'status' => preg_match_all('#https?://|www\.#i', $body) > 2 ? 'spam' : 'pending',
            'is_admin' => false,
            'ip_hash' => $ip,
        ]);
        if ($c->status === 'pending') $this->notifyComment($post, $c);
        return redirect($back)->with('comment_ok', 'Thanks! Your comment is waiting for approval.');
    }
    private function notifyComment($post, $c): void
    {
        try {
            $s = Setting::map();
            if (empty($s['mail_host']) || empty($s['email'])) return;
            MailConfig::apply($s);
            Mail::raw("A new comment on \"{$post->title}\" is waiting for your approval.\n\nFrom: {$c->name} ({$c->email})\n\n{$c->body}\n\nReview it here: " . url('/admin/comments'), fn($m) => $m->to($s['email'])->subject('New comment awaiting approval'));
        } catch (\Throwable $e) {
            report($e);
        }
    }
    public function like(Request $r, string $slug)
    {
        $post = Post::where('published', 1)->where('slug', $slug)->firstOrFail();
        $v = $this->visitor($r);
        $row = DB::table('post_likes')->where('post_id', $post->id)->where('visitor', $v);
        if ($row->exists()) {
            $row->delete();
            $liked = false;
        } else {
            DB::table('post_likes')->insertOrIgnore(['post_id' => $post->id, 'visitor' => $v, 'created_at' => now()]);
            $liked = true;
        }
        return response()->json(['liked' => $liked, 'count' => DB::table('post_likes')->where('post_id', $post->id)->count()]);
    }
    public function share(string $slug)
    {
        Post::where('published', 1)->where('slug', $slug)->increment('share_count');
        return response()->noContent();
    }
    public function sitemap()
    {
        return response()->view('sitemap', ['posts' => Post::where('published', 1)->get(), 'services' => $this->q('services')->get(), 'projects' => $this->q('projects')->get(), 'cats' => $this->cats()])->header('Content-Type', 'application/xml');
    }
    public function contact(Request $r)
    {
        $d = $r->validate(['name' => 'required|min:2|max:100', 'email' => 'required|email|max:150', 'phone' => 'nullable|max:40', 'subject' => 'required|min:2|max:150', 'message' => 'required|min:10|max:3000', 'website' => 'max:0']);
        $src = $this->src($r);
        Entry::of('messages')->newQuery()->create(Arr::only($d, ['name', 'email', 'phone', 'subject', 'message']) + $src);
        try {
            $s = Setting::map();
            MailConfig::apply($s);
            $to = $s['email'] ?? config('portfolio.email');
            Mail::raw("Name: {$d['name']}\nEmail: {$d['email']}\nPhone: " . ($d['phone'] ?? '') . "\nCame from: {$src['source']}\n\n{$d['message']}", fn($m) => $m->to($to)->replyTo($d['email'])->subject('Portfolio: ' . $d['subject']));
        } catch (\Throwable $e) {
            report($e);
        }
        return redirect('/contact')->with('sent', 'Thank you! Your message has been sent.');
    }
}
