@extends('layouts.app')
@php $name = $s['name'] ?? $p['name']; @endphp
@section('title', 'Unsubscribe | ' . $name)
@section('desc', 'Manage your email subscription.')
@push('head')
    <meta name="robots" content="noindex">
@endpush
@section('content')
    @include('sections.pagehead', ['title' => 'Unsubscribe', 'sub' => 'Manage your email subscription.'])
    <section class="sec">
        <div class="container text-center" style="max-width:560px">
            @if (!$row)
                <p class="text-body-secondary">This unsubscribe link is not valid.</p>
                <a class="btn btn-primary" href="{{ url('/') }}">Back to home</a>
            @elseif($done)
                <div class="alert alert-success">You have been unsubscribed. You will not receive any more emails.</div>
                <a class="btn btn-primary" href="{{ url('/') }}">Back to home</a>
            @else
                <p class="text-body-secondary">Do you want to stop receiving emails at <strong>{{ $row->email }}</strong>?
                </p>
                <form method="post" action="{{ url('/unsubscribe/' . $row->token) }}">@csrf<button
                        class="btn btn-primary btn-lg">Yes, unsubscribe me</button> <a class="btn btn-ghost btn-lg"
                        href="{{ url('/') }}">Cancel</a></form>
            @endif
        </div>
    </section>
@endsection
