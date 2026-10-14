@extends('layouts.app')
@php $name = $s['name'] ?? $p['name']; @endphp
@section('title', 'Projects | ' . $name)
@section('desc', 'Websites and digital projects designed and developed by ' . $name . '.')
@section('content')
    @include('sections.pagehead', [
        'title' => 'Projects',
        'sub' => 'Websites and digital projects I have designed and developed.',
    ])
    <section class="sec">
        <div class="container">
            <div class="pills mb-4" data-filters data-target="#pg"><button class="pill on" data-f="All">All</button>
                @foreach (config('admin.projects.f.category.2') as $c)
                    <button class="pill" data-f="{{ $c }}">{{ $c }}</button>
                @endforeach
            </div>
            <div class="row g-4" id="pg">
                @forelse($projects as $i => $pr)
                    @include('sections.project-card')
                @empty
                    <div class="col-12">
                        <div class="card p-4 text-center text-body-secondary">No projects yet. Add them from Admin &gt;
                            Projects.</div>
                    </div>
                @endforelse
            </div>
            <div class="mt-5 d-flex justify-content-center">{{ $projects->links() }}</div>
        </div>
    </section>
@endsection
