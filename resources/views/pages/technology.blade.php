@extends('layouts.app')
@use('App\Support\Ui')
@php $name = $s['name'] ?? $p['name']; @endphp
@section('title', 'Technology Stack | ' . $name)
@section('desc',
    'Technologies and tools: HTML5, CSS3, JavaScript, Bootstrap, PHP, Laravel, MySQL, SEO and digital
    marketing tools.')
@section('content')
    @include('sections.pagehead', [
        'title' => 'Technology Stack',
        'sub' => 'The tools and technologies I use to design, build and promote websites.',
    ])
    <section class="sec">
        <div class="container">
            <div class="pills mb-4" data-filters data-target="#stack-g"><button class="pill on" data-f="All">All</button>
                @foreach ($tech->pluck('category')->unique() as $c)
                    <button class="pill" data-f="{{ $c }}">{{ $c }}</button>
                @endforeach
            </div>
            <div class="row g-3" id="stack-g">
                @foreach ($tech as $i => $t)
                    <div class="col-6 col-md-4 col-lg-3" data-cat="{{ $t->category }}">
                        <div class="card tech h-100 p-3 rv" style="--d:{{ ($i % 4) * 0.06 }}s">
                            <div class="tic"><span>{{ mb_substr($t->name, 0, 2) }}</span>
                                @if (Ui::logo($t->name))
                                    <img src="{{ Ui::logo($t->name) }}" alt="" width="30" height="30"
                                        loading="lazy" onerror="this.remove()">
                                @endif
                            </div>
                            <h3 class="h6 mt-3 mb-1">{{ $t->name }}</h3><span
                                class="small text-primary fw-semibold">{{ $t->category }}</span>
                            <p class="small text-body-secondary mb-0 mt-1">{{ $t->description }}</p>
                            @if ($t->level)
                                <span class="chip mt-2 align-self-start">{{ $t->level }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <section class="sec alt">
        <div class="container">
            <p class="eyebrow">Skills</p>
            <h2 class="sec-title">IT &amp; digital skills</h2>
            <div class="row g-3 mt-2">
                @foreach ($skills as $i => $g)
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 p-4 rv">
                            <h3 class="h6 mb-3">{{ $g->title }}</h3>
                            <div>
                                @foreach (Ui::list($g->items) as $it)
                                    <span class="chip">{{ $it }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
