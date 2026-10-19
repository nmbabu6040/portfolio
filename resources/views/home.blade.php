@extends('layouts.app')
@use('App\Support\Ui')
@php
    $name = $s['name'] ?? $p['name'];
    $role = $s['role'] ?? $p['role'];
    $intro = $s['intro'] ?? $p['intro'];
    $aboutText = $s['about'] ?? implode("\n\n", $p['about']);
    $ini = Ui::initials($name);
    $pillIcons = ['bi-briefcase', 'bi-graph-up-arrow', 'bi-cpu', 'bi-globe2'];
@endphp
@section('title', $s['seo_title'] ?? $name . ' | Sales & Marketing Professional | Web Developer & Digital Marketing')
@section('desc', $s['seo_description'] ?? Ui::plain($intro, 160))
@push('head')
    <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@graph' => [['@type' => 'Person', 'name' => $name, 'jobTitle' => $role, 'url' => url('/'), 'sameAs' => array_values(array_filter([$s['linkedin'] ?? null, $s['github'] ?? null, $s['facebook'] ?? null]))], ['@type' => 'WebSite', 'name' => $name, 'url' => url('/')]]], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush
@section('content')
    <section id="home" class="hero">
        <div class="blob b1"></div>
        <div class="blob b2"></div>
        <div class="container position-relative">
            <div class="row align-items-center g-5">
                <div class="col-lg-7 order-2 order-lg-1">
                    <p class="eyebrow-l">Personal Portfolio</p>
                    <h1 class="hero-title">{{ $name }}</h1>
                    <p class="hero-sub"><span class="visually-hidden">{{ $role }}</span><span class="gt"
                            id="rot" data-words="{{ $role }}"
                            aria-hidden="true">{{ trim(explode('|', $role)[0]) }}</span></p>
                    <div class="hero-text">{!! Ui::rich($intro) !!}</div>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a class="btn btn-primary btn-lg" href="{{ url('/projects') }}">View My Portfolio <i
                                class="bi bi-arrow-right ms-1"></i></a>
                        <a class="btn btn-ghost-light btn-lg" href="{{ url('/about') }}">About Me</a><a
                            class="btn btn-ghost-light btn-lg" href="{{ url('/contact') }}">Contact Me</a>
                        @if (!empty($s['cv']))
                            <a class="btn btn-ghost-light btn-lg" href="{{ asset($s['cv']) }}" download><i
                                    class="bi bi-download me-1"></i>CV</a>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-4"><span class="chip-l">Since 2007</span><span
                            class="chip-l">Sales &amp; Marketing</span><span class="chip-l">Web Design &amp;
                            Development</span><span class="chip-l">SEO</span></div>
                </div>
                <div class="col-lg-5 order-1 order-lg-2 text-center">
                    <div class="frame">
                        @if (!empty($s['photo']))
                            {!! Ui::pic($s['photo'], 'Portrait of ' . $name, '(max-width:487px) 78vw, 380px', '', 'high') !!}@else<div class="frame-ph">{{ $ini }}</div>
                        @endif
                        <div class="float-chip"><i class="bi bi-trophy"></i> 18+ years in Sales &amp; Marketing</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="marq" aria-hidden="true">
        <div class="marq-track">
            @for ($k = 0; $k < 2; $k++)
                @foreach ($tech->take(16) as $t)
                    <span class="marq-i">
                        @if (Ui::logo($t->name))
                            <img src="{{ Ui::logo($t->name) }}" alt="" width="22" height="22" loading="lazy"
                                onerror="this.remove()">
                        @endif{{ $t->name }}
                    </span>
                @endforeach
            @endfor
        </div>
    </div>

    <section class="sec">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <p class="eyebrow">About me</p>
                    <h2 class="sec-title">Business knowledge meets technology</h2>
                    <p class="text-body-secondary">{{ Ui::plain($aboutText, 280) }}</p>
                    <a class="btn btn-primary mt-2" href="{{ url('/about') }}">More about me <i
                            class="bi bi-arrow-right ms-1"></i></a> <a class="btn btn-ghost mt-2"
                        href="{{ url('/experience') }}">My experience</a>
                </div>
                <div class="col-lg-6">
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

    <section class="sec alt">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <p class="eyebrow">Services</p>
                    <h2 class="sec-title mb-0">How I can help your business</h2>
                </div><a class="btn btn-ghost" href="{{ url('/services') }}">All services</a>
            </div>
            <div class="row g-4">
                @foreach ($services as $i => $sv)
                    @include('sections.service-card')
                @endforeach
            </div>
        </div>
    </section>

    <section class="sec">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <p class="eyebrow">Portfolio</p>
                    <h2 class="sec-title mb-0">Featured projects</h2>
                </div><a class="btn btn-ghost" href="{{ url('/projects') }}">All projects</a>
            </div>
            <div class="row g-4">
                @forelse($projects as $i => $pr)
                    @include('sections.project-card')@empty
                    <div class="col-12">
                        <div class="card p-4 text-center text-body-secondary">Projects will appear here. Add them from Admin
                            &gt; Projects.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    @include('sections.reviews')
    @include('sections.stats')
@endsection
