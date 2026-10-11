@extends('layouts.app')
@php
    $u = urlencode(url()->current());
    $tt = urlencode($post->title);
    $desc = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 150);
@endphp
@section('title', $post->seo_title ?: $post->title . ' | ' . ($s['name'] ?? config('portfolio.name')))
@section('desc', $post->seo_description ?: $desc)
@section('og_type', 'article')
@if ($post->image)
    @section('og_image', asset($post->image))
@endif
@push('head')
    <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => $post->title, 'datePublished' => $post->created_at->toIso8601String(), 'image' => $post->image ? asset($post->image) : null, 'author' => ['@type' => 'Person', 'name' => $s['name'] ?? config('portfolio.name')], 'mainEntityOfPage' => url()->current()], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush
@section('content')
    <article class="py-5">
        <div class="container" style="max-width:780px">
            <p class="lbl">{{ $post->created_at->format('Y-m-d') }}</p>
            <h1>{{ $post->title }}</h1>
            @if ($post->image)
                <img src="{{ asset($post->image) }}" class="img-fluid rounded mb-3" alt="{{ $post->title }}">
            @endif
            <div class="post-body">{!! \App\Support\Html::render($post->body) !!}</div>
            <hr>
            <h2 class="h6">Share this post</h2>
            <div class="d-flex flex-wrap gap-2">
                @foreach (['Facebook' => "https://www.facebook.com/sharer/sharer.php?u=$u", 'X' => "https://twitter.com/intent/tweet?url=$u&text=$tt", 'LinkedIn' => "https://www.linkedin.com/sharing/share-offsite/?url=$u", 'WhatsApp' => "https://wa.me/?text=$tt%20$u"] as $n => $l)
                    <a class="btn btn-outline-primary" target="_blank" rel="noopener noreferrer"
                        href="{{ $l }}">{{ $n }}</a>
                @endforeach
            </div>
        </div>
    </article>
@endsection
