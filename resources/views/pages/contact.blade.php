@extends('layouts.app')
@php
    $name = $s['name'] ?? $p['name'];
    $em = $s['email'] ?? $p['email'];
@endphp
@section('title', 'Contact | ' . $name)
@section('desc', 'Have a project, business opportunity or collaboration in mind? Get in touch with ' . $name . '.')
@section('content')
    @include('sections.pagehead', [
        'title' => 'Contact',
        'sub' => "Have a project, business opportunity or collaboration in mind? Let's connect.",
    ])
    <section class="sec">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5">
                    <h2 class="sec-title">Get in touch</h2>
                    <p class="text-body-secondary">Send a message and I will get back to you.</p>
                    <a class="info" href="mailto:{{ $em }}"><i
                            class="bi bi-envelope"></i><span>{{ $em }}</span></a>
                    @if (!empty($s['phone']))
                        <a class="info" href="tel:{{ preg_replace('/[^\d+]/', '', $s['phone']) }}"><i
                                class="bi bi-telephone"></i><span>{{ $s['phone'] }}</span></a>
                    @endif
                    @if (!empty($s['whatsapp']))
                        <a class="info" href="https://wa.me/{{ preg_replace('/\D/', '', $s['whatsapp']) }}"
                            target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp"></i><span>WhatsApp:
                                {{ $s['whatsapp'] }}</span></a>
                    @endif
                    <a class="info" href="{{ url('/quote') }}">
                        <i class="bi bi-receipt"></i>
                        <span>Need a price? Request a quote</span>
                    </a>
                    @if (!empty($s['booking_url']))
                        <a class="info" {!! \App\Support\Ui::book($s) !!}><i class="bi bi-calendar-check"></i><span>Book a free
                                call</span></a>
                    @endif
                    <div class="d-flex gap-2 mt-3">
                        @foreach (['linkedin', 'github', 'facebook'] as $k)
                            @if (!empty($s[$k]))
                                <a class="soc" href="{{ $s[$k] }}" target="_blank" rel="noopener noreferrer"
                                    aria-label="{{ ucfirst($k) }}"><i class="bi bi-{{ $k }}"></i></a>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="col-lg-7">
                    <form method="post" action="{{ url('/contact') }}" class="card p-4" novalidate>@csrf
                        @if (session('sent'))
                            <div class="alert alert-success" role="status">{{ session('sent') }}</div>
                        @endif
                        <input name="website" tabindex="-1" autocomplete="off" aria-hidden="true"
                            style="position:absolute;left:-9999px">
                        <div class="row g-3">
                            @foreach (['name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'subject' => 'Subject'] as $k => $l)
                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $k }}">{{ $l }}</label>
                                    <input class="form-control @error($k) is-invalid @enderror" id="{{ $k }}"
                                        name="{{ $k }}"
                                        value="{{ old($k, $k == 'subject' ? request('subject') : '') }}"
                                        @if ($k == 'email') type="email" @endif>
                                    <div class="invalid-feedback">{{ $errors->first($k) }}</div>
                                </div>
                            @endforeach
                            <div class="col-12">
                                <label class="form-label" for="message">Message</label>
                                <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5">{{ old('message') }}</textarea>
                                <div class="invalid-feedback">{{ $errors->first('message') }}</div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary btn-lg">Send Message <i class="bi bi-send ms-1"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
