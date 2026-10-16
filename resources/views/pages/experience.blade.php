@extends('layouts.app')
@use('App\Support\Ui')
@php $name = $s['name'] ?? $p['name']; @endphp
@section('title', 'Experience | ' . $name)
@section('desc',
    'Professional experience in Sales & Marketing since 2007, with education, certifications and
    achievements.')
@section('content')
    @include('sections.pagehead', [
        'title' => 'Experience',
        'sub' =>
            'Professional journey in Sales & Marketing since 2007, alongside practical IT and digital skills.',
    ])
    <section class="sec">
        <div class="container">
            <div style="max-width:820px">
                @foreach ($exp as $e)
                    <div class="tl-item rv">
                        <div class="card p-4">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <h3 class="h5 mb-1">{{ $e->title }}</h3><span class="chip">{{ $e->period }}</span>
                            </div>
                            @if ($e->org)
                                <p class="text-body-secondary mb-2">{{ $e->org }}</p>
                            @endif
                            <div>
                                @foreach (Ui::list($e->areas) as $a)
                                    <span class="chip">{{ $a }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @if ($edu->count() || $certs->count() || $ach->count())
        <section class="sec alt">
            <div class="container">
                <p class="eyebrow">Background</p>
                <h2 class="sec-title">Education, certifications &amp; achievements</h2>
                <div class="row g-3 mt-2">
                    @foreach ($edu as $i => $e)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 p-4 rv"><i class="bi bi-mortarboard fs-3 text-primary"></i>
                                <h3 class="h6 mt-2">{{ $e->title }}</h3>
                                <p class="small text-body-secondary mb-1">{{ $e->institution }} {{ $e->period }}</p>
                                <div class="small text-body-secondary rich">{!! \App\Support\Html::render((string) $e->details) !!}</div>
                            </div>
                        </div>
                    @endforeach
                    @foreach ($certs as $e)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 p-4 rv"><i class="bi bi-patch-check fs-3 text-success"></i>
                                <h3 class="h6 mt-2">
                                    @if ($e->url)
                                        <a href="{{ $e->url }}" target="_blank"
                                            rel="noopener noreferrer">{{ $e->title }}</a>@else{{ $e->title }}
                                    @endif
                                </h3>
                                <p class="small text-body-secondary mb-0">{{ $e->issuer }} {{ $e->year }}</p>
                            </div>
                        </div>
                    @endforeach
                    @foreach ($ach as $e)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 p-4 rv"><i class="bi bi-award fs-3 text-warning"></i>
                                <h3 class="h6 mt-2">{{ $e->title }} {{ $e->year }}</h3>
                                <div class="small text-body-secondary rich">{!! \App\Support\Html::render((string) $e->details) !!}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
