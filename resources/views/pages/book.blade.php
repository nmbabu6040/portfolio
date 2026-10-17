@extends('layouts.app')
@php
    $name = $s['name'] ?? $p['name'];
    $u = $s['booking_url'];
@endphp
@section('title', 'Book a call | ' . $name)
@section('desc', 'Pick a time for a call with ' . $name . '.')
@section('content')
    @include('sections.pagehead', ['title' => 'Book a call', 'sub' => 'Pick a time that suits you.'])
    <section class="sec">
        <div class="container">
            @if (!empty($s['booking_embed']))
                <div class="card p-2 overflow-hidden"><iframe src="{{ $u }}" title="Book a call" loading="lazy"
                        style="width:100%;height:780px;border:0"></iframe></div>
                <p class="small text-body-secondary mt-3">Calendar not loading? <a href="{{ $u }}" target="_blank"
                        rel="noopener noreferrer">Open it in a new tab</a>.</p>
            @else
                <div class="text-center" style="max-width:520px;margin:auto">
                    <p class="text-body-secondary">Choose a time on my calendar.</p><a class="btn btn-primary btn-lg"
                        href="{{ $u }}" target="_blank" rel="noopener noreferrer">Open the calendar</a>
                </div>
            @endif
        </div>
    </section>
@endsection
