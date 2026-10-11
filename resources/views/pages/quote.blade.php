@extends('layouts.app')
@php
    $name = $s['name'] ?? $p['name'];
    $pre = request('service');
    $deadlines = ['', 'As soon as possible', 'Within 2 weeks', 'Within a month', '1 to 3 months', 'Flexible'];
@endphp
@section('title', 'Request a Quote | ' . $name)
@section('desc', 'Tell me about your project and get a quote for web design, development, SEO, digital marketing or
    graphics work.')
@section('content')
    @include('sections.pagehead', [
        'title' => 'Request a Quote',
        'sub' => 'Tell me about your project. I will review it and reply with the next steps and a quote.',
    ])
    <section class="sec">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <h2 class="h4 mb-3">What happens next</h2>
                    @foreach ([['bi-send', 'You send the request', 'Share what you need, your budget idea and your timeline.'], ['bi-search', 'I review it', 'I read your details and may ask a few questions.'], ['bi-reply', 'You get a reply', 'I reply by email with the plan and a quote.']] as $i => [$ic, $t, $d])
                        <div class="d-flex gap-3 mb-3">
                            <div class="sic flex-shrink-0" style="width:46px;height:46px;font-size:1.2rem;margin:0"><i
                                    class="bi {{ $ic }}"></i></div>
                            <div>
                                <h3 class="h6 mb-1">{{ $i + 1 }}. {{ $t }}</h3>
                                <p class="small text-body-secondary mb-0">{{ $d }}</p>
                            </div>
                        </div>
                    @endforeach
                    @if (!empty($s['whatsapp']))
                        <a class="info" href="https://wa.me/{{ preg_replace('/\D/', '', $s['whatsapp']) }}"
                            target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp"></i><span>Prefer WhatsApp?
                                Message me</span></a>
                    @endif
                    <a class="info" href="{{ url('/contact') }}"><i class="bi bi-chat-dots"></i><span>Just have a
                            question? Contact me</span></a>
                </div>
                <div class="col-lg-8">
                    <form method="post" action="{{ url('/quote') }}" class="card p-4" novalidate>@csrf
                        @if (session('sent'))
                            <div class="alert alert-success" role="status">{{ session('sent') }}</div>
                        @endif
                        <input name="website" tabindex="-1" autocomplete="off" aria-hidden="true"
                            style="position:absolute;left:-9999px">
                        <div class="row g-3">
                            @foreach (['name' => 'Your name', 'email' => 'Email', 'phone' => 'Phone / WhatsApp (optional)'] as $k => $l)
                                <div class="col-md-6"><label class="form-label"
                                        for="{{ $k }}">{{ $l }}</label><input
                                        class="form-control @error($k) is-invalid @enderror" id="{{ $k }}"
                                        name="{{ $k }}" value="{{ old($k) }}"
                                        @if ($k == 'email') type="email" @endif>
                                    <div class="invalid-feedback">{{ $errors->first($k) }}</div>
                                </div>
                            @endforeach
                            <div class="col-md-6"><label class="form-label" for="service">Service needed</label><select
                                    class="form-select form-control @error('service') is-invalid @enderror" id="service"
                                    name="service">
                                    <option value="">Select a service</option>
                                    @foreach ($services as $sv)
                                        <option value="{{ $sv->title }}" @selected(old('service', $pre) == $sv->title)>
                                            {{ $sv->title }}</option>
                                    @endforeach
                                    <option value="Other" @selected(old('service', $pre) == 'Other')>Other</option>
                                </select>
                                <div class="invalid-feedback">{{ $errors->first('service') }}</div>
                            </div>
                            <div class="col-md-6"><label class="form-label" for="budget">Budget (optional)</label><input
                                    class="form-control" id="budget" name="budget" value="{{ old('budget') }}"
                                    placeholder="e.g. BDT 30,000 or USD 300"></div>
                            <div class="col-md-6"><label class="form-label" for="deadline">Timeline
                                    (optional)</label><select class="form-select form-control" id="deadline"
                                    name="deadline">
                                    @foreach ($deadlines as $d)
                                        <option value="{{ $d }}" @selected(old('deadline') == $d)>
                                            {{ $d ?: 'Select a timeline' }}</option>
                                    @endforeach
                                </select></div>
                            <div class="col-12"><label class="form-label" for="message">Project details</label>
                                <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="6"
                                    placeholder="What do you want to build or achieve? Who is it for? Any websites you like?">{{ old('message') }}</textarea>
                                <div class="invalid-feedback">{{ $errors->first('message') }}</div>
                            </div>
                            <div class="col-12"><button class="btn btn-primary btn-lg">Send request <i
                                        class="bi bi-send ms-1"></i></button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
