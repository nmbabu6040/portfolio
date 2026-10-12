@extends('errors.shell')
@section('code', '429')
@section('title', 'Too many requests')
@section('message', 'You are sending requests too quickly. Please wait a minute and try again.')
@section('actions')
    <a class="b p" href="javascript:history.back()">Go back</a><a class="b" href="{{ url('/') }}">Home</a>
@endsection
