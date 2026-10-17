<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="csrf" content="{{ csrf_token() }}">
    <title>Admin</title>
    <link rel="icon" href="{{ asset(\App\Models\Setting::map()['favicon'] ?? 'favicon.ico') }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
</head>

<body class="adm">
    @auth
        @php
            $user = auth()->user();
            $can = fn($p) => $user->hasPerm($p);
            $unread = $can('messages')
                ? \App\Models\Entry::of('messages')->newQuery()->where('is_read', 0)->count()
                : 0;
            $pending =
                $can('posts.all') && \Illuminate\Support\Facades\Schema::hasTable('comments')
                    ? \App\Models\Entry::of('comments')->newQuery()->where('status', 'pending')->count()
                    : 0;
            $logo = \App\Models\Setting::map()['logo'] ?? null;
            $ic = [
                'projects' => 'bi-kanban',
                'posts' => 'bi-journal-text',
                'categories' => 'bi-tags',
                'services' => 'bi-grid',
                'faqs' => 'bi-question-circle',
                'testimonials' => 'bi-chat-quote',
                'experiences' => 'bi-briefcase',
                'educations' => 'bi-mortarboard',
                'certifications' => 'bi-patch-check',
                'achievements' => 'bi-award',
                'technologies' => 'bi-cpu',
                'skills' => 'bi-stars',
                'workflows' => 'bi-diagram-3',
                'stats' => 'bi-bar-chart',
                'reasons' => 'bi-check2-circle',
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
                        <a class="sb-brand" href="/admin"><span class="sb-logo {{ $logo ? 'has-img' : '' }}">
                                @if ($logo)
                                    <img src="{{ asset($logo) }}" alt=""
                                    style="width:100%;height:100%;object-fit:contain">@else<i
                                        class="bi bi-speedometer2"></i>
                                @endif
                            </span>
                            <span><b>Admin Panel</b><small>Portfolio CMS</small></span></a>
                        <button class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas"
                            data-bs-target="#side" aria-label="Close"></button>
                    </div>
                    <nav>
                        <div class="sb-group">Overview</div>
                        @if ($can('content'))
                            <a class="sb-link {{ $on('/admin') ? 'on' : '' }}" href="/admin"><i
                                    class="bi bi-grid-1x2"></i>Dashboard</a>
                        @endif
                        @if ($can('settings'))
                            <a class="sb-link {{ $on('/admin/settings') ? 'on' : '' }}" href="/admin/settings"><i
                                    class="bi bi-person-gear"></i>Profile &amp; Settings</a>
                        @endif
                        @if ($can('media'))
                            <a class="sb-link {{ $on('/admin/media') ? 'on' : '' }}" href="/admin/media"><i
                                    class="bi bi-images"></i>Media library</a>
                        @endif
                        @foreach ($groups as $g => $keys)
                            @php $vis = array_values(array_filter($keys, fn ($k) => $can($k === 'posts' ? 'posts.own' : 'content'))); @endphp
                            @if (count($vis))
                                <div class="sb-group">{{ $g }}</div>
                                @foreach ($vis as $k)
                                    <a class="sb-link {{ $on('/admin/' . $k) ? 'on' : '' }}"
                                        href="/admin/{{ $k }}"><i
                                            class="bi {{ $ic[$k] }}"></i>{{ config('admin.' . $k . '.label') }}</a>
                                @endforeach
                            @endif
                        @endforeach
                        @if ($can('messages'))
                            <div class="sb-group">Inbox</div>
                            <a class="sb-link {{ $on('/admin/leads') ? 'on' : '' }}" href="/admin/leads"><i
                                    class="bi bi-kanban"></i>Leads pipeline</a>
                            <a class="sb-link {{ $on('/admin/conversions') ? 'on' : '' }}" href="/admin/conversions"><i
                                    class="bi bi-bullseye"></i>Conversions</a>
                            <a class="sb-link {{ $on('/admin/messages') ? 'on' : '' }}" href="/admin/messages"><i
                                    class="bi bi-envelope"></i>Messages @if ($unread)
                                    <span class="badge text-bg-danger">{{ $unread }}</span>
                                @endif
                            </a>
                            <a class="sb-link {{ $on('/admin/subscribers') ? 'on' : '' }}" href="/admin/subscribers"><i
                                    class="bi bi-people"></i>Subscribers</a>
                        @endif
                        @if ($can('posts.own'))
                            <a class="sb-link {{ $on('/admin/comments') ? 'on' : '' }}" href="/admin/comments"><i
                                    class="bi bi-chat-dots"></i>Comments @if ($pending)
                                    <span class="badge text-bg-warning">{{ $pending }}</span>
                                @endif
                            </a>
                        @endif
                        <div class="sb-group">Account</div>
                        <a class="sb-link {{ $on('/admin/password') ? 'on' : '' }}" href="/admin/password"><i
                                class="bi bi-shield-lock"></i>Password</a>
                        <a class="sb-link {{ $on('/admin/security') ? 'on' : '' }}" href="/admin/security"><i
                                class="bi bi-phone"></i>Two-factor (2FA)</a>
                        @if ($can('users'))
                            <a class="sb-link {{ $on('/admin/users') ? 'on' : '' }}" href="/admin/users"><i
                                    class="bi bi-person-badge"></i>Users &amp; roles</a>
                        @endif
                        @if ($can('activity'))
                            <a class="sb-link {{ $on('/admin/activity') ? 'on' : '' }}" href="/admin/activity"><i
                                    class="bi bi-clock-history"></i>Activity log</a>
                        @endif
                        @if ($can('backup'))
                            <a class="sb-link {{ $on('/admin/backup') ? 'on' : '' }}" href="/admin/backup"><i
                                    class="bi bi-cloud-download"></i>Backup</a>
                        @endif
                    </nav>
                    <div class="sb-foot">
                        <div class="sb-user">
                            <span class="avatar">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <b>{{ $user->name }}</b><small>{{ config('roles.labels')[$user->role] ?? $user->role }}
                                    · {{ $user->email }}</small>
                            </div>
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
                        @if ($can('messages'))
                            <a class="bell" href="/admin/messages" aria-label="Messages"><i class="bi bi-bell"></i>
                                @if ($unread)
                                    <span class="badge rounded-pill text-bg-danger">{{ $unread }}</span>
                                @endif
                            </a>
                        @endif
                        <div class="dropdown"><button class="btn btn-primary" data-bs-toggle="dropdown"><i
                                    class="bi bi-plus-lg me-1"></i>New</button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                @if ($can('content'))
                                    <li><a class="dropdown-item" href="/admin/projects/create">Project</a></li>
                                @endif
                                <li><a class="dropdown-item" href="/admin/posts/create">Blog post</a></li>
                                @if ($can('content'))
                                    <li><a class="dropdown-item" href="/admin/services/create">Service</a></li>
                                    <li><a class="dropdown-item" href="/admin/experiences/create">Experience</a></li>
                                @endif
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
        <div class="modal fade" id="picker" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title h5">Choose an image</h2><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="picker-grid" class="pgrid"></div>
                        <p id="picker-empty" class="text-body-secondary d-none mb-0">No images in the library yet. Upload
                            some in the Media library, or use the file box.</p>
                    </div>
                    <div class="modal-footer"><a class="btn btn-outline-secondary me-auto" href="/admin/media"
                            target="_blank">Open Media library</a><button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">Cancel</button></div>
                </div>
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
