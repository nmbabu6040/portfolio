<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <meta name="description" content="@yield('desc')">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" type="image/x-icon" href="{{ $s['favicon'] }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title')">
    <meta property="og:description" content="@yield('desc')">
    <meta property="og:url" content="{{ url()->current() }}">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    @if (!empty($s['gsc_verify']))
        <meta name="google-site-verification" content="{{ $s['gsc_verify'] }}">
    @endif
    @if (!empty($s['ga_id']))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $s['ga_id'] }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments)
            }
            gtag('js', new Date());
            gtag('config', '{{ $s['ga_id'] }}');
        </script>
    @endif
    @stack('head')
    <script>
        try {
            var t = localStorage.getItem('th');
            if (t) document.documentElement.setAttribute('data-bs-theme', t)
        } catch (e) {}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
</head>
@php
    $nm = $s['name'] ?? config('portfolio.name');
    $ini = \App\Support\Ui::initials($nm);
    $nav = [
        'about' => 'About',
        'experience' => 'Experience',
        'technology' => 'Technology',
        'projects' => 'Projects',
        'services' => 'Services',
        'blog' => 'Blog',
        'contact' => 'Contact',
    ];
@endphp

<body>
    <div id="prog"></div>
    <nav class="navbar navbar-expand-xl sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}"><span
                    class="logo-m">{{ $ini }}</span><span
                    class="brand d-none d-sm-inline">{{ $nm }}</span></a>
            <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Menu"><i
                    class="bi bi-list fs-2"></i></button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav ms-auto align-items-xl-center gap-xl-1">
                    <li class="nav-item"><a class="nav-link {{ request()->is('/') ? 'active' : '' }}"
                            href="{{ url('/') }}">Home</a></li>
                    @foreach ($nav as $k => $l)
                        <li class="nav-item"><a
                                class="nav-link {{ request()->is($k) || request()->is($k . '/*') ? 'active' : '' }}"
                                href="{{ url($k) }}">{{ $l }}</a></li>
                    @endforeach
                    <li class="nav-item ms-xl-2"><button class="btn btn-ghost btn-sm px-3" id="theme"
                            aria-label="Toggle dark mode"><i class="bi bi-circle-half"></i></button></li>
                </ul>
            </div>
        </div>
    </nav>
    <main>@yield('content')</main>

    <section class="foot-cta">
        <div class="container">
            <div class="cta-box">
                <div>
                    <h2 class="h3 mb-1">Have a project, business opportunity or collaboration in mind?</h2>
                    <p class="mb-0 opacity-75">Let's connect and talk about how I can help.</p>
                </div>
                <div class="d-flex flex-wrap gap-2"><a class="btn btn-light btn-lg" href="{{ url('/contact') }}">Let's
                        connect <i class="bi bi-arrow-right ms-1"></i></a>
                    @if (!empty($s['cv']))
                        <a class="btn btn-ghost-light btn-lg" href="{{ asset($s['cv']) }}" download><i
                                class="bi bi-download me-1"></i>Download CV</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
    <footer class="foot">
        <div class="container pt-5 pb-3">
            <div class="row g-4 g-lg-5">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3"><span
                            class="logo-m">{{ $ini }}</span><span
                            class="brand text-white fs-5">{{ $nm }}</span></div>
                    <p>{{ \Illuminate\Support\Str::limit($s['intro'] ?? config('portfolio.intro'), 170) }}</p>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        @foreach (['linkedin' => 'LinkedIn', 'github' => 'GitHub', 'facebook' => 'Facebook'] as $k => $l)
                            @if (!empty($s[$k]))
                                <a class="soc" href="{{ $s[$k] }}" target="_blank" rel="noopener noreferrer"
                                    aria-label="{{ $l }}"><i class="bi bi-{{ $k }}"></i></a>
                            @endif
                        @endforeach
                        @if (!empty($s['email']))
                            <a class="soc" href="mailto:{{ $s['email'] }}" aria-label="Email"><i
                                    class="bi bi-envelope"></i></a>
                        @endif
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h3 class="fh">Quick links</h3>
                    <a class="foot-link" href="{{ url('/') }}">Home</a>
                    @foreach ($nav as $k => $l)
                        <a class="foot-link" href="{{ url($k) }}">{{ $l }}</a>
                    @endforeach
                </div>
                <div class="col-6 col-lg-3">
                    <h3 class="fh">Services</h3>
                    @forelse($fs as $sv)
                        <a class="foot-link"
                        href="{{ url('/services/' . $sv->slug) }}">{{ $sv->title }}</a>@empty<a
                            class="foot-link" href="{{ url('/services') }}">All services</a>
                    @endforelse
                </div>
                <div class="col-lg-3">
                    <h3 class="fh">Contact</h3>
                    @if (!empty($s['email']))
                        <a class="foot-link" href="mailto:{{ $s['email'] }}"><i
                                class="bi bi-envelope me-2"></i>{{ $s['email'] }}</a>
                    @endif
                    @if (!empty($s['phone']))
                        <a class="foot-link" href="tel:{{ preg_replace('/[^\d+]/', '', $s['phone']) }}"><i
                                class="bi bi-telephone me-2"></i>{{ $s['phone'] }}</a>
                    @endif
                    <a class="foot-link" href="{{ url('/contact') }}"><i class="bi bi-chat-dots me-2"></i>Send a
                        message</a>
                    @if ($fp->count())
                        <h3 class="fh mt-4">Latest from the blog</h3>
                        @foreach ($fp as $po)
                            <a class="foot-link"
                                href="{{ url('/blog/' . $po->slug) }}">{{ \Illuminate\Support\Str::limit($po->title, 40) }}</a>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="foot-bar mt-4 pt-3 d-flex flex-wrap justify-content-between gap-2 small">
                <span>© {{ date('Y') }} {{ $nm }}. All rights reserved.</span>
                <span><a href="{{ url('/blog') }}">Blog</a> · <a href="{{ url('/sitemap.xml') }}">Sitemap</a> ·
                    Sales &amp; Marketing since 2007</span>
            </div>
        </div>
    </footer>

    <div class="modal fade" id="lb" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content p-2"><img id="lbi" class="img-fluid rounded"
                    alt="Project screenshot"><button class="btn btn-ghost mt-2"
                    data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
    <button class="btn btn-primary d-none" id="top" aria-label="Back to top"><i
            class="bi bi-arrow-up"></i></button>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/site.js') }}"></script>
</body>

</html>
