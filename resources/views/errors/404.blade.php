@extends('errors.shell')
@section('code', '404')
@section('title', 'Page not found')
@section('message', 'The page you are looking for does not exist or has been moved.')
@section('actions')
    <a class="b p" href="{{ url('/') }}">Back to home</a><a class="b" href="{{ url('/projects') }}">Projects</a><a
        class="b" href="{{ url('/blog') }}">Blog</a><a class="b" href="{{ url('/contact') }}">Contact</a>
@endsection
