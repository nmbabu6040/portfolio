@extends('layouts.app')
@php $name = $s['name'] ?? $p['name']; @endphp
@section('title', 'Services | ' . $name)
@section('desc', 'Web design and development, digital marketing, SEO, social media marketing, graphics design and data
    entry services.')
@section('content')
    @include('sections.pagehead', [
        'title' => 'Services',
        'sub' => 'Practical services that combine business sense with digital skills.',
    ])
    <section class="sec">
        <div class="container">
            <div class="row g-4">
                @foreach ($services as $i => $sv)
                    @include('sections.service-card')
                @endforeach
            </div>
        </div>
    </section>
    <section class="sec alt">
        <div class="container">
            <p class="eyebrow">Workflow</p>
            <h2 class="sec-title">A clear, professional process</h2>
            <div class="row g-3 mt-2">
                @foreach ($flow as $i => $w)
                    <div class="col-md-6 col-lg-4">
                        <div class="card step h-100 p-4 rv" style="--d:{{ ($i % 3) * 0.08 }}s">
                            <div class="step-n">0{{ $i + 1 }}</div>
                            <h3 class="h5">{{ $w->title }}</h3>
                            <p class="text-body-secondary mb-0">{{ $w->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
