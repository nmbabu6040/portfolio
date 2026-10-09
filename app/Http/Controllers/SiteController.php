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
    public function home()
    {
        return $this->v('home', [
            'tech' => $this->q('technologies')->where('visible', 1)->get(),
            'services' => $this->q('services')->get(),
            'projects' => $this->q('projects')->take(3)->get(),
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
        return $this->v('pages.projects', ['projects' => $this->q('projects')->get()]);
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
    public function blog()
    {
        return view('blog.index', ['s' => Setting::map(), 'posts' => Post::where('published', 1)->latest()->paginate(10)]);
    }
    public function post(string $slug)
    {
        return view('blog.show', ['s' => Setting::map(), 'post' => Post::where('published', 1)->where('slug', $slug)->firstOrFail()]);
    }
    public function sitemap()
    {
        return response()->view('sitemap', ['posts' => Post::where('published', 1)->get(), 'services' => $this->q('services')->get(), 'projects' => $this->q('projects')->get()])->header('Content-Type', 'application/xml');
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
