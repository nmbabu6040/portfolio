@extends('layouts.app')
@php
    $name = $s['name'] ?? $p['name'];
    $about = isset($s['about']) ? preg_split('/\R{2,}/', $s['about']) : $p['about'];
    $pillIcons = ['bi-briefcase', 'bi-graph-up-arrow', 'bi-cpu', 'bi-globe2'];
@endphp
@section('title', 'About ' . $name . ' | Sales & Marketing Professional')
@section('desc', \Illuminate\Support\Str::limit($about[0] ?? '', 155))
@section('content')
    @include('sections.pagehead', ['title' => 'About Me', 'sub' => $s['intro'] ?? $p['intro']])
    <section class="sec">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-7">
                    @foreach ($about as $t)
                        <p class="text-body-secondary fs-5">{{ $t }}</p>
                    @endforeach
                    <div class="d-flex flex-wrap gap-2 mt-3"><a class="btn btn-primary" href="{{ url('/experience') }}">View
                            experience</a><a class="btn btn-ghost" href="{{ url('/contact') }}">Get in touch</a>
                        @if (!empty($s['cv']))
                            <a class="btn btn-ghost" href="{{ asset($s['cv']) }}" download>Download CV</a>
                        @endif
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="row g-3">
                        @foreach ($p['pillars'] as $i => $t)
                            <div class="col-6">
                                <div class="tile rv" style="--d:{{ $i * 0.08 }}s"><i
                                        class="bi {{ $pillIcons[$i % 4] }}"></i>
                                    <h3 class="h6 mb-0">{{ $t }}</h3>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('sections.stats')
    <section class="sec alt">
        <div class="container">
            <p class="eyebrow">Why work with me</p>
            <h2 class="sec-title">Business sense, backed by technology</h2>
            <div class="row g-3 mt-2">
                @foreach ($why as $i => $w)
                    <div class="col-md-6 col-lg-4">
                        <div class="why rv" style="--d:{{ ($i % 3) * 0.06 }}s"><i
                                class="bi bi-check-circle-fill"></i><span>{{ $w->title }}</span></div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
