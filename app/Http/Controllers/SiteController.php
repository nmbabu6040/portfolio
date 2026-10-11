<?php

namespace App\Http\Controllers;

use App\Models\{Entry, Setting, Post};
use App\Support\MailConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;

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
        return $this->v('pages.projects', ['projects' => Entry::of('projects')->newQuery()->orderBy('sort')->orderByDesc('id')->get()]);
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
    public function privacy()
    {
        return $this->v('pages.privacy');
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
        Entry::of('messages')->newQuery()->create([
            'name' => $d['name'],
            'email' => $d['email'],
            'phone' => $d['phone'] ?? null,
            'subject' => 'Quote request: ' . $d['service'],
            'message' => $d['message'],
            'kind' => 'quote',
            'service' => $d['service'],
            'budget' => $d['budget'] ?? null,
            'deadline' => $d['deadline'] ?? null
        ]);
        try {
            $s = Setting::map();
            MailConfig::apply($s);
            $to = $s['email'] ?? config('portfolio.email');
            Mail::raw("New quote request\n\nService: {$d['service']}\nBudget: " . ($d['budget'] ?? '-') . "\nDeadline: " . ($d['deadline'] ?? '-') . "\n\nName: {$d['name']}\nEmail: {$d['email']}\nPhone: " . ($d['phone'] ?? '') . "\n\n{$d['message']}", fn($m) => $m->to($to)->replyTo($d['email'])->subject('Quote request: ' . $d['service']));
        } catch (\Throwable $e) {
            report($e);
        }
        return redirect('/quote')->with('sent', 'Thank you! I have received your request and will reply with details soon.');
    }
    public function blog()
    {
        return view('blog.index', ['s' => Setting::map(), 'posts' => Post::where('published', 1)->latest()->paginate(9), 'cats' => $this->cats(), 'active' => null]);
    }
    public function category(string $slug)
    {
        $cat = Entry::of('categories')->newQuery()->where('slug', $slug)->firstOrFail();
        return view('blog.index', ['s' => Setting::map(), 'posts' => Post::where('published', 1)->where('category', $slug)->latest()->paginate(9), 'cats' => $this->cats(), 'active' => $cat]);
    }
    public function post(string $slug)
    {
        $post = Post::where('published', 1)->where('slug', $slug)->firstOrFail();
        $related = Post::where('published', 1)->where('id', '!=', $post->id)
            ->when($post->category, fn($q) => $q->orderByRaw('category = ? desc', [$post->category]))->latest()->limit(3)->get();
        $cat = $post->category ? Entry::of('categories')->newQuery()->where('slug', $post->category)->first() : null;
        return view('blog.show', ['s' => Setting::map(), 'post' => $post, 'related' => $related, 'cat' => $cat]);
    }
    public function sitemap()
    {
        return response()->view('sitemap', ['posts' => Post::where('published', 1)->get(), 'services' => $this->q('services')->get(), 'projects' => $this->q('projects')->get(), 'cats' => $this->cats()])->header('Content-Type', 'application/xml');
    }
    public function contact(Request $r)
    {
        $d = $r->validate(['name' => 'required|min:2|max:100', 'email' => 'required|email|max:150', 'phone' => 'nullable|max:40', 'subject' => 'required|min:2|max:150', 'message' => 'required|min:10|max:3000', 'website' => 'max:0']);
        Entry::of('messages')->newQuery()->create(Arr::only($d, ['name', 'email', 'phone', 'subject', 'message']));
        try {
            $s = Setting::map();
            MailConfig::apply($s);
            $to = $s['email'] ?? config('portfolio.email');
            Mail::raw("Name: {$d['name']}\nEmail: {$d['email']}\nPhone: " . ($d['phone'] ?? '') . "\n\n{$d['message']}", fn($m) => $m->to($to)->replyTo($d['email'])->subject('Portfolio: ' . $d['subject']));
        } catch (\Throwable $e) {
            report($e);
        }
        return redirect('/contact')->with('sent', 'Thank you! Your message has been sent.');
    }
}
