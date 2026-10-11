@extends('layouts.app')
@php
    $nm = $s['name'] ?? config('portfolio.name');
    $t = $active ? $active->title : null;
@endphp
@section('title', ($t ? $t . ' | ' : '') . 'Blog | ' . $nm)
@section('desc', $t ? 'Blog posts about ' . $t . ' by ' . $nm . '.' : 'Articles, tips and updates by ' . $nm . '.')
@section('content')
    @include(
        'sections.pagehead',
        ['title' => $t ?: 'Blog', 'sub' => $t ? 'Posts in this category.' : 'Articles, tips and updates.'] +
            ($t ? ['crumb' => ['Blog', 'blog']] : []))
    <section class="sec">
        <div class="container">
            @if ($cats->count())
                <div class="pills mb-4"><a class="pill {{ $active ? '' : 'on' }}" href="{{ url('/blog') }}">All</a>
                    @foreach ($cats as $c)
                        <a class="pill {{ $active && $active->slug == $c->slug ? 'on' : '' }}"
                            href="{{ url('/blog/category/' . $c->slug) }}">{{ $c->title }} ({{ $c->n }})</a>
                    @endforeach
                </div>
            @endif
            <div class="row g-4">
                @forelse($posts as $i => $po)
                    @include('sections.post-card')@empty
                    <div class="col-12">
                        <div class="card p-5 text-center text-body-secondary">No posts here yet.</div>
                    </div>
                @endforelse
            </div>
            <div class="mt-5 d-flex justify-content-center">{{ $posts->links() }}</div>
        </div>
    </section>
@endsection
