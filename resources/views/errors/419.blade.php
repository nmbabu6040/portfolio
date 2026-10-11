@extends('layouts.app')
@php $s = \App\Models\Setting::map(); @endphp
@section('title', 'Page expired')
@section('desc', 'Your session has expired.')
@section('content')
    <section class="sec">
        <div class="container text-center py-5">
            <h1 class="h2 mb-2">Page expired</h1>
            <p class="text-body-secondary mb-4">Your session timed out before the form was sent. Please go back, refresh the
                page and try again.</p>
            <a class="btn btn-primary btn-lg" href="{{ url('/contact') }}">Back to contact form</a>
        </div>
    </section>
@endsection
