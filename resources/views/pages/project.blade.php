@extends('layouts.app')
@use('App\Support\Ui')
@php $name = $s['name'] ?? $p['name']; @endphp
@section('title', $pr->seo_title ?: $pr->title . ' | ' . $name)
@section('desc', $pr->seo_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $pr->description), 155))
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
                            aria-label="Enlarge screenshot">{!! \App\Support\Ui::pic(
                                $pr->image,
                                $pr->title . ' screenshot',
                                '(min-width:992px) 760px, 100vw',
                                'img-fluid rounded-4 w-100',
                                'high',
                            ) !!}</button>
                    @endif
                    <div class="fs-5 text-body-secondary rich">{!! \App\Support\Html::render((string) $pr->description) !!}</div>
                    @if ($pr->challenge || $pr->solution || $pr->result)
                        <div class="mt-4">
                            @foreach ([['The challenge', 'bi-exclamation-diamond', $pr->challenge], ['What I did', 'bi-tools', $pr->solution], ['The result', 'bi-graph-up-arrow', $pr->result]] as [$ct, $ci, $cv])
                                @if ($cv)
                                    <div class="cs-b">
                                        <h2 class="h5 mb-3"><i
                                                class="bi {{ $ci }} me-2 text-primary"></i>{{ $ct }}
                                        </h2>
                                        <div class="post-body">{!! \App\Support\Html::render((string) $cv) !!}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    @if ($pr->details)
                        <div class="post-body mt-4">{!! \App\Support\Html::render($pr->details) !!}</div>
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
                    <div class="card p-4 mb-3">
                        <h2 class="h6">Project info</h2>
                        @foreach ([['Client', $pr->client], ['Year', $pr->year], ['My role', $pr->my_role], ['Category', $pr->category]] as [$il, $iv])
                            @if ($iv)
                                <p class="mb-1 small text-body-secondary">{{ $il }}</p>
                                <p class="fw-semibold">{{ $iv }}</p>
                            @endif
                        @endforeach
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
                                <a class="btn btn-ghost" href="{{ url('/projects') }}">
                                    All projects</a>
                        </div>
                    </div>
                    <div class="card p-4">
                        <h2 class="h6">Need something similar?</h2>
                        <p class="small text-body-secondary">Tell me about your project and I will reply with the next
                            steps.</p>
                        <div class="d-grid gap-2"><a class="btn btn-primary" href="{{ url('/quote') }}">Request a quote</a>
                            @if (!empty($s['booking_url']))
                                <a class="btn btn-ghost" {!! \App\Support\Ui::book($s) !!}><i
                                        class="bi bi-calendar-check me-1"></i>Book a call</a>
                            @endif
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
