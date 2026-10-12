@extends('errors.shell')
@section('code', '401')
@section('title', 'Please sign in')
@section('message', 'You need to sign in to see this page.')
@section('actions')
    <a class="b p" href="{{ url('/admin/login') }}">Admin login</a><a class="b" href="{{ url('/') }}">Home</a>
@endsection
