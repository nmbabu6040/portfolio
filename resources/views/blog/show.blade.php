@extends('layouts.app')
@php
    $nm = $s['name'] ?? config('portfolio.name');
    $link = url()->current();
    $desc = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 150);
    $og = null;
    if ($post->image) {
        $jpg = preg_replace('/\.webp$/', '.jpg', $post->image);
        $og = file_exists(public_path($jpg)) ? $jpg : $post->image;
    }
    $mins = max(1, (int) ceil(str_word_count(strip_tags($post->body)) / 200));
@endphp
@section('title', $post->seo_title ?: $post->title . ' | ' . $nm)
@section('desc', $post->seo_description ?: $desc)
@section('og_type', 'article')
@if ($og)
    @section('og_image', asset($og))
@endif
@push('head')
    <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => $post->title, 'datePublished' => $post->created_at->toIso8601String(), 'dateModified' => $post->updated_at->toIso8601String(), 'image' => $og ? asset($og) : null, 'articleSection' => $cat->title ?? null, 'author' => ['@type' => 'Person', 'name' => $nm], 'mainEntityOfPage' => $link], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush
@section('content')
    <article class="py-5">
        <div class="container" style="max-width:780px">
            @if ($cat)
                <a class="badge text-bg-primary text-decoration-none mb-2"
                    href="{{ url('/blog/category/' . $cat->slug) }}">{{ $cat->title }}</a>
            @endif
            <p class="lbl">{{ $post->created_at->format('d M Y') }} · {{ $mins }} min read</p>
            <h1>{{ $post->title }}</h1>
            @if ($post->has_affiliate)
                <div class="aff-note"><i class="bi bi-info-circle"></i><span>Disclosure: this post contains affiliate links.
                        If you buy or sign up through them, I may earn a commission at no extra cost to you. <a
                            href="{{ url('/affiliate-disclosure') }}">Learn more</a></span></div>
            @endif
            @if ($post->image)
                <img src="{{ asset($post->image) }}" class="img-fluid rounded-4 mb-4 w-100" alt="{{ $post->title }}">
            @endif
            <div class="post-body">{!! \App\Support\Html::render($post->body) !!}</div>
            <hr class="my-4">
            @include('sections.share', ['shareUrl' => $link, 'shareTitle' => $post->title])
            <a class="btn btn-ghost mt-4" href="{{ url('/blog') }}"><i class="bi bi-arrow-left me-1"></i>All posts</a>
        </div>
    </article>
    @if ($related->count())
        <section class="sec alt">
            <div class="container">
                <h2 class="h3 mb-4">Related posts</h2>
                <div class="row g-4">
                    @foreach ($related as $i => $po)
                        @include('sections.post-card')
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
