@extends('layouts.app')
@use('App\Support\Ui')
@php $name = $s['name'] ?? $p['name']; @endphp
@section('title', $sv->seo_title ?: $sv->title . ' | ' . $name)
@section('desc', $sv->seo_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($sv->description ?:
    $sv->details)), 155))
@section('content')
    @include('sections.pagehead', [
        'title' => $sv->title,
        'sub' => $sv->description,
        'crumb' => ['Services', 'services'],
    ])
    <section class="sec">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-8">
                    <div class="sic mb-3"><i class="bi {{ Ui::icon($sv->icon, $sv->title) }}"></i></div>
                    <div class="post-body">{!! $sv->details ? \App\Support\Html::render($sv->details) : '<p>' . e($sv->description) . '</p>' !!}</div>
                    <a class="btn btn-primary btn-lg mt-3"
                        href="{{ url('/quote') }}?service={{ urlencode($sv->title) }}">Request a quote <i
                            class="bi bi-arrow-right ms-1"></i></a> <a class="btn btn-ghost btn-lg mt-3"
                        href="{{ url('/contact') }}?subject={{ urlencode($sv->title) }}">Ask a question</a>
                    @include('sections.faq')
                </div>
            </div>
            <aside class="col-lg-4">
                <div class="card p-4">
                    <h2 class="h6 mb-3">Other services</h2>
                    @foreach ($others as $o)
                        <a class="d-block py-2 text-decoration-none border-bottom"
                            href="{{ url('/services/' . $o->slug) }}"><i
                                class="bi {{ Ui::icon($o->icon, $o->title) }} me-2 text-primary"></i>{{ $o->title }}</a>
                    @endforeach
                    <a class="btn btn-ghost mt-3" href="{{ url('/services') }}">All services</a>
                </div>
            </aside>
        </div>
        </div>
    </section>
@endsection
