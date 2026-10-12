@extends('layouts.app')
@php
    $nm = $s['name'] ?? config('portfolio.name');
    $t = $active ? $active->title : null;
    $q = $q ?? '';
@endphp
@section('title', ($t ? $t . ' | ' : '') . 'Blog | ' . $nm)
@section('desc', $t ? 'Blog posts about ' . $t . ' by ' . $nm . '.' : 'Articles, tips and updates by ' . $nm . '.')
@if ($q !== '')
    @push('head')
        <meta name="robots" content="noindex, follow">
    @endpush
@endif
@section('content')
    @include(
        'sections.pagehead',
        ['title' => $t ?: 'Blog', 'sub' => $t ? 'Posts in this category.' : 'Articles, tips and updates.'] +
            ($t ? ['crumb' => ['Blog', 'blog']] : []))
    <section class="sec">
        <div class="container">
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
                @if ($cats->count())
                    <div class="pills"><a class="pill {{ $active || $q !== '' ? '' : 'on' }}"
                            href="{{ url('/blog') }}">All</a>
                        @foreach ($cats as $c)
                            <a class="pill {{ $active && $active->slug == $c->slug ? 'on' : '' }}"
                                href="{{ url('/blog/category/' . $c->slug) }}">{{ $c->title }} ({{ $c->n }})</a>
                        @endforeach
                    </div>
                @else<span></span>
                @endif
                <form action="{{ url('/blog') }}" method="get" class="bsearch" role="search">
                    <div class="input-group">
                        <input class="form-control" type="search" name="q" value="{{ $q }}"
                            placeholder="Search posts" aria-label="Search posts" maxlength="80">
                        <button class="btn btn-primary" aria-label="Search"><i class="bi bi-search"></i></button>
                    </div>
                </form>
            </div>
            @if ($q !== '')
                <p class="text-body-secondary">{{ $posts->total() }} result(s) for "<strong>{{ $q }}</strong>" <a
                        class="ms-2" href="{{ url('/blog') }}">Clear search</a></p>
            @endif
            <div class="row g-4">
                @forelse($posts as $i => $po)
                    @include('sections.post-card')@empty
                    <div class="col-12">
                        <div class="card p-5 text-center text-body-secondary">
                            {{ $q !== '' ? 'No posts match your search.' : 'No posts here yet.' }}</div>
                    </div>
                @endforelse
            </div>
            <div class="mt-5 d-flex justify-content-center">{{ $posts->links() }}</div>
        </div>
    </section>
@endsection
