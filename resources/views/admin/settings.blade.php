@extends('admin.base')
@section('c')
    @php
        $icons = [
            'SEO' => 'bi-search',
            'Analytics' => 'bi-graph-up',
            'Email (SMTP)' => 'bi-envelope-at',
            'Affiliate' => 'bi-link-45deg',
        ];
    @endphp
    <div class="mb-4">
        <h1 class="h4 mb-0">Profile &amp; Settings</h1>
        <p class="text-body-secondary small mb-0">Everything shown on your website</p>
    </div>
    <form method="post" enctype="multipart/form-data" id="sf">@csrf
        <div class="row g-4">
            <div class="col-xl-7 d-flex flex-column gap-4">
                <div class="acard p-4">
                    <h2 class="h6 mb-3"><i class="bi bi-person-circle me-2 text-primary"></i>Profile</h2>
                    <div class="fgrid">@include('admin.fields', ['f' => $g['Profile'], 'v' => $s])</div>
                </div>
                <div class="acard p-4">
                    <h2 class="h6 mb-1"><i class="bi bi-shield-check me-2 text-primary"></i>Legal pages</h2>
                    <p class="small text-body-secondary mb-3">Leave a field empty to show the default text on that page
                        (/privacy and /affiliate-disclosure).</p>
                    @include('admin.fields', ['f' => $g['Legal'], 'v' => $s])
                </div>
            </div>
            <div class="col-xl-5 d-flex flex-column gap-4">
                @foreach ($g as $title => $f)
                    @continue(in_array($title, ['Profile', 'Legal']))
                    <div class="acard p-4">
                        <h2 class="h6 mb-3"><i
                                class="bi {{ $icons[$title] ?? 'bi-gear' }} me-2 text-primary"></i>{{ $title }}</h2>
                        @include('admin.fields', ['f' => $f, 'v' => $s])
                        @if (str_starts_with($title, 'Email'))
                            <button type="submit" form="testmail" class="btn btn-outline-secondary btn-sm"><i
                                    class="bi bi-send me-1"></i>Send test email</button>
                            <div class="form-text">Save the settings first, then test.</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="savebar"><button class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save all
                settings</button></div>
    </form>
    <form id="testmail" method="post" action="/admin/settings/test-mail">@csrf</form>
@endsection
