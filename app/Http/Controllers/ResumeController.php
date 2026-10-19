<?php

namespace App\Http\Controllers;

use App\Models\{Entry, Setting};
use Illuminate\Support\Str;

class ResumeController extends Controller
{
    private function data(): array
    {
        $s = Setting::map();
        $public = !empty($s['resume_enabled']);
        abort_unless($public || (auth()->check() && auth()->user()->hasPerm('settings')), 404);
        $q = fn(string $t) => Entry::of($t)->newQuery()->orderBy('sort')->orderBy('id');
        $n = max(0, min(8, (int) ($s['resume_projects'] ?? 4)));
        return [
            's' => $s,
            'p' => config('portfolio'),
            'preview' => !$public,
            'exp' => $q('experiences')->get(),
            'edu' => $q('educations')->get(),
            'certs' => $q('certifications')->get(),
            'ach' => $q('achievements')->get(),
            'skills' => $q('skills')->get(),
            'tech' => $q('technologies')->where('visible', 1)->get()->groupBy('category'),
            'projects' => $n ? Entry::of('projects')->newQuery()->orderBy('sort')->orderByDesc('id')->limit($n)->get() : collect(),
        ];
    }
    public function show()
    {
        return view('resume.show', $this->data());
    }
    public function pdf()
    {
        abort_unless(class_exists(\Dompdf\Dompdf::class), 404);
        $d = $this->data();
        $pdf = new \Dompdf\Dompdf(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false]);
        $pdf->loadHtml(view('resume.pdf', $d)->render(), 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();
        $file = Str::slug($d['s']['name'] ?? $d['p']['name']) . '-resume.pdf';
        return response($pdf->output(), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="' . $file . '"']);
    }
}
