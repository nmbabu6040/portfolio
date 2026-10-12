@extends('errors.shell')
@section('code', '500')
@section('title', 'Something went wrong')
@section('message', 'A problem happened on my side, it is not your fault. Please try again in a few minutes.')
@section('actions')
    <a class="b p" href="javascript:location.reload()">Try again</a><a class="b" href="{{ url('/') }}">Home</a><a
        class="b" href="{{ url('/contact') }}">Contact me</a>
@endsection
