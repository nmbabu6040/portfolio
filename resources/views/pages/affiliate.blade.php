@extends('layouts.app')
@php
    $name = $s['name'] ?? $p['name'];
    $em = $s['email'] ?? $p['email'];
@endphp
@section('title', 'Affiliate Disclosure | ' . $name)
@section('desc', 'How affiliate links work on this website and how they may earn a commission.')
@section('content')
    @include('sections.pagehead', [
        'title' => 'Affiliate Disclosure',
        'sub' => 'How affiliate links work on this website.',
    ])
    <section class="sec">
        <div class="container">
            <div class="post-body" style="max-width:820px">
                @if (!empty($s['affiliate_disclosure']))
                    {!! \App\Support\Html::render($s['affiliate_disclosure']) !!}
                @else
                    <h2>Affiliate links</h2>
                    <p>Some links on this website are affiliate links. If you click one and buy a product or sign up for a
                        service, I may earn a commission. This comes at no extra cost to you.</p>
                    <h2>My approach</h2>
                    <p>I aim to recommend only products and services that I believe are useful. My opinions are my own, and
                        a commission does not decide what I write. Posts that contain affiliate links show a short notice at
                        the top.</p>
                    <h2>Questions</h2>
                    <p>If you have any questions about this disclosure, you can contact me at <a
                            href="mailto:{{ $em }}">{{ $em }}</a>.</p>
                @endif
            </div>
        </div>
    </section>
@endsection
