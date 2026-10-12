@extends('errors.shell')
@section('code', '503')
@section('title', 'Temporarily unavailable')
@section('message', 'The website is temporarily unavailable. Please check back in a little while.')
@section('actions')
    <a class="b p" href="javascript:location.reload()">Try again</a>
@endsection
