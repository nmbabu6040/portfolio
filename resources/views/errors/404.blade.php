@extends('layouts.app')
@php $s = \App\Models\Setting::map(); @endphp
@section('title', 'Page not found')
@section('desc', 'The page you are looking for does not exist.')
@section('content')
    <section class="sec">
        <div class="container text-center py-5">
            <div
                style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:clamp(4rem,14vw,8rem);line-height:1;background:linear-gradient(135deg,#4f46e5,#7c3aed 55%,#06b6d4);-webkit-background-clip:text;background-clip:text;color:transparent">
                404</div>
            <h1 class="h2 mb-2">Page not found</h1>
            <p class="text-body-secondary mb-4">The page you are looking for does not exist or has been moved.</p>
            <div class="d-flex flex-wrap justify-content-center gap-2"><a class="btn btn-primary btn-lg"
                    href="{{ url('/') }}">Back to home</a><a class="btn btn-ghost btn-lg"
                    href="{{ url('/projects') }}">View projects</a><a class="btn btn-ghost btn-lg"
                    href="{{ url('/contact') }}">Contact me</a></div>
        </div>
    </section>
@endsection
