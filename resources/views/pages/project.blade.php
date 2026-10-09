@extends('layouts.app')
@use('App\Support\Ui')
@php $name = $s['name'] ?? $p['name']; @endphp
@section('title', $pr->title . ' | ' . $name)
@section('desc', \Illuminate\Support\Str::limit(strip_tags((string) $pr->description), 155))
@if ($pr->image)
    @section('og_image', asset($pr->image))
@endif
@section('content')
    @include('sections.pagehead', [
        'title' => $pr->title,
        'sub' => $pr->category,
        'crumb' => ['Projects', 'projects'],
    ])
    <section class="sec">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-8">
                    @if ($pr->image)
                        <button class="btn p-0 border-0 mb-4 w-100" data-lb="{{ asset($pr->image) }}"
                            aria-label="Enlarge screenshot"><img src="{{ asset($pr->image) }}"
                                class="img-fluid rounded-4 w-100" alt="{{ $pr->title }} screenshot"></button>
                    @endif
                    <p class="fs-5 text-body-secondary">{{ $pr->description }}</p>
                    @if ($pr->details)
                        <div class="post-body">{!! \App\Support\Html::render($pr->details) !!}</div>
                    @endif
                    @if ($pr->features)
                        <h2 class="h5 mt-4">Features</h2>
                        <ul>
                            @foreach (Ui::list($pr->features, "\n") as $f)
                                <li>{{ $f }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <aside class="col-lg-4">
                    <div class="card p-4">
                        <h2 class="h6">Project info</h2>
                        <p class="mb-1 small text-body-secondary">Category</p>
                        <p class="fw-semibold">{{ $pr->category }}</p>
                        @if ($pr->tech)
                            <p class="mb-1 small text-body-secondary">Technologies</p>
                            <div class="mb-3">
                                @foreach (Ui::list($pr->tech) as $t)
                                    <span class="chip">{{ $t }}</span>
                                @endforeach
                            </div>
                        @endif
                        <div class="d-grid gap-2">
                            @if ($pr->demo)
                                <a class="btn btn-primary" href="{{ $pr->demo }}" target="_blank"
                                    rel="noopener noreferrer">Live Demo</a>
                                @endif @if ($pr->code)
                                    <a class="btn btn-ghost" href="{{ $pr->code }}" target="_blank"
                                        rel="noopener noreferrer">Source Code</a>
                                @endif
                                <a class="btn btn-ghost" href="{{ url('/projects') }}">All projects</a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
    @if ($related->count())
        <section class="sec alt">
            <div class="container">
                <h2 class="sec-title mb-4">Related projects</h2>
                <div class="row g-4">
                    @foreach ($related as $i => $pr)
                        @include('sections.project-card')
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
