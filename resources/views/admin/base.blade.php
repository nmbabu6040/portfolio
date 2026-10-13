<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="csrf" content="{{ csrf_token() }}">
    <title>Admin</title>
    {{-- <link rel="icon" type="image/x-icon" href="{{ $s['favicon'] }}"> --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">

    <link rel="icon" href="{{ asset(\App\Models\Setting::map()['favicon'] ?? 'favicon.ico') }}">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
</head>

<body class="adm">
    @auth
        @php
            $unread = \App\Models\Entry::of('messages')->newQuery()->where('is_read', 0)->count();
            $user = auth()->user();
            $logo = \App\Models\Setting::map()['logo'] ?? null;
            $ic = [
                'projects' => 'bi-kanban',
                'posts' => 'bi-journal-text',
                'services' => 'bi-grid',
                'experiences' => 'bi-briefcase',
                'educations' => 'bi-mortarboard',
                'certifications' => 'bi-patch-check',
                'achievements' => 'bi-award',
                'technologies' => 'bi-cpu',
                'skills' => 'bi-stars',
                'workflows' => 'bi-diagram-3',
                'stats' => 'bi-bar-chart',
                'reasons' => 'bi-check2-circle',
                'faqs' => 'bi-question-circle',
                'testimonials' => 'bi-chat-quote',
                'categories' => 'bi-tags',
            ];
            $groups = [
                'Content' => ['projects', 'posts', 'categories', 'services', 'faqs', 'testimonials'],
                'Career' => ['experiences', 'educations', 'certifications', 'achievements'],
                'Site data' => ['technologies', 'skills', 'workflows', 'stats', 'reasons'],
            ];
            $on = fn($u) => $u == '/admin' ? request()->is('admin') : request()->is(ltrim($u, '/') . '*');
            $crumb = ucwords(str_replace('-', ' ', request()->segment(2) ?? 'dashboard'));
        @endphp
        <div class="d-lg-flex min-vh-100">
            <aside class="offcanvas-lg offcanvas-start sb" tabindex="-1" id="side">
                <div class="sb-in">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <a class="sb-brand" href="/admin">
                            <span class="sb-logo {{ $logo ? 'has-img' : '' }}">
                                @if ($logo)
                                    <img src="{{ asset($logo) }}" alt=""
                                    style="width:100%;height:100%;object-fit:contain">@else<i
                                        class="bi bi-speedometer2"></i>
                                @endif
                            </span>
                            <span><b>Admin Panel</b><small>Portfolio CMS</small></span>
                        </a>
                        <button class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas"
                            data-bs-target="#side" aria-label="Close"></button>
                    </div>
                    <nav>
                        <div class="sb-group">Overview</div>
                        <a class="sb-link {{ $on('/admin') ? 'on' : '' }}" href="/admin"><i
                                class="bi bi-grid-1x2"></i>Dashboard</a>
                        <a class="sb-link {{ $on('/admin/settings') ? 'on' : '' }}" href="/admin/settings"><i
                                class="bi bi-person-gear"></i>Profile &amp; Settings</a>
                        <a class="sb-link {{ $on('/admin/media') ? 'on' : '' }}" href="/admin/media"><i
                                class="bi bi-images"></i>Media library</a>
                        @foreach ($groups as $g => $keys)
                            <div class="sb-group">{{ $g }}</div>
                            @foreach ($keys as $k)
                                <a class="sb-link {{ $on('/admin/' . $k) ? 'on' : '' }}"
                                    href="/admin/{{ $k }}"><i
                                        class="bi {{ $ic[$k] }}"></i>{{ config('admin.' . $k . '.label') }}</a>
                            @endforeach
                        @endforeach
                        <div class="sb-group">Inbox</div>
                        <a class="sb-link {{ $on('/admin/messages') ? 'on' : '' }}" href="/admin/messages"><i
                                class="bi bi-envelope"></i>Messages @if ($unread)
                                <span class="badge text-bg-danger">{{ $unread }}</span>
                            @endif
                        </a>
                        <a class="sb-link {{ $on('/admin/subscribers') ? 'on' : '' }}" href="/admin/subscribers"><i
                                class="bi bi-people"></i>Subscribers</a>
                        <div class="sb-group">Account</div>
                        <a class="sb-link {{ $on('/admin/password') ? 'on' : '' }}" href="/admin/password"><i
                                class="bi bi-shield-lock"></i>Password</a>

                        <a class="sb-link {{ $on('/admin/security') ? 'on' : '' }}" href="/admin/security"><i
                                class="bi bi-phone"></i>Two-factor (2FA)</a>

                        <a class="sb-link {{ $on('/admin/backup') ? 'on' : '' }}" href="/admin/backup"><i
                                class="bi bi-cloud-download"></i>Backup</a>
                    </nav>
                    <div class="sb-foot">
                        <div class="sb-user"><span class="avatar">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                            <div class="min-w-0"><b>{{ $user->name }}</b><small>{{ $user->email }}</small></div>
                        </div>
                        <div class="d-flex gap-2"><a class="btn btn-outline-light btn-sm flex-fill" href="/"
                                target="_blank"><i class="bi bi-box-arrow-up-right me-1"></i>Site</a>
                            <form method="post" action="/admin/logout" class="flex-fill">@csrf<button
                                    class="btn btn-outline-danger btn-sm w-100"><i
                                        class="bi bi-power me-1"></i>Logout</button></form>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="adm-main">
                <div class="topbar">
                    <button class="btn btn-dark d-lg-none px-3" data-bs-toggle="offcanvas" data-bs-target="#side"
                        aria-label="Open menu"><i class="bi bi-list"></i></button>
                    <div class="crumb">Admin <span>/</span> {{ $crumb }}</div>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <a class="bell" href="/admin/messages" aria-label="Messages"><i class="bi bi-bell"></i>
                            @if ($unread)
                                <span class="badge rounded-pill text-bg-danger">{{ $unread }}</span>
                            @endif
                        </a>
                        <div class="dropdown"><button class="btn btn-primary" data-bs-toggle="dropdown"><i
                                    class="bi bi-plus-lg me-1"></i>New</button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item" href="/admin/projects/create">Project</a></li>
                                <li><a class="dropdown-item" href="/admin/posts/create">Blog post</a></li>
                                <li><a class="dropdown-item" href="/admin/services/create">Service</a></li>
                                <li><a class="dropdown-item" href="/admin/experiences/create">Experience</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <main class="adm-content">
                    @if (session('ok'))
                        <div class="alert alert-success border-0 shadow-sm">{{ session('ok') }}</div>
                    @endif
                    @if (session('err'))
                        <div class="alert alert-danger border-0 shadow-sm">{{ session('err') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm"><strong>Not saved. Please fix:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @yield('c')
                </main>
            </div>
        </div>
    @else
        <div class="login-wrap">
            <div class="w-100" style="max-width:420px">@yield('c')</div>
        </div>
    @endauth
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script src="{{ asset('js/admin.js') }}"></script>
</body>

</html>
