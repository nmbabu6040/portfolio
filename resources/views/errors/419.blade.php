@extends('errors.shell')
@section('code', '419')
@section('title', 'Page expired')
@section('message', 'Your session timed out before the form was sent. Please go back, refresh the page and try again.')
@section('actions')
    <a class="b p" href="javascript:history.back()">Go back</a><a class="b" href="{{ url('/') }}">Home</a>
@endsection
