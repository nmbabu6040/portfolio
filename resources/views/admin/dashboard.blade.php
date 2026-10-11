@extends('admin.base')
@section('c')
    @php
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
        ];
        $check = [
            ['Profile photo uploaded', !empty($s['photo']), '/admin/settings'],
            ['CV uploaded', !empty($s['cv']), '/admin/settings'],
            ['Contact email set', !empty($s['email']), '/admin/settings'],
            ['SEO title and description', !empty($s['seo_title']) && !empty($s['seo_description']), '/admin/settings'],
            ['At least one project', $counts['projects'] > 0, '/admin/projects/create'],
            ['At least one blog post', $counts['posts'] > 0, '/admin/posts/create'],
            ['Google Analytics connected', !empty($s['ga_id']), '/admin/settings'],
            ['SMTP email configured', !empty($s['mail_host']), '/admin/settings'],
        ];
        $done = collect($check)->where(1, true)->count();
        $pct = round(($done / count($check)) * 100);
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Welcome back, {{ explode(' ', $s['name'] ?? auth()->user()->name)[0] }} 👋</h1>
            <p class="text-body-secondary mb-0">{{ now()->format('l, d F Y') }} · Here is what is happening on your site.</p>
        </div>
        <div class="d-flex flex-wrap gap-2"><a class="btn btn-primary" href="/admin/projects/create"><i
                    class="bi bi-plus-lg me-1"></i>Project</a><a class="btn btn-primary" href="/admin/posts/create"><i
                    class="bi bi-pencil-square me-1"></i>Write post</a><a class="btn btn-outline-secondary"
                href="/admin/settings"><i class="bi bi-person-gear me-1"></i>Profile</a></div>
    </div>

    <div class="row g-3 mb-4">
        @foreach ([['Projects', $counts['projects'], 'bi-kanban', 'g1', '/admin/projects', 'published on your site'], ['Blog posts', $counts['posts'], 'bi-journal-text', 'g2', '/admin/posts', $n['drafts'] . ' draft'], ['Messages', $n['messages'], 'bi-envelope', 'g3', '/admin/messages', $n['unread'] . ' unread'], ['Services', $counts['services'], 'bi-grid', 'g4', '/admin/services', 'with their own pages']] as [$l, $v, $i, $g, $u, $sub])
            <div class="col-6 col-xl-3"><a href="{{ $u }}" class="acard stat h-100"><span
                        class="stat-i {{ $g }}"><i class="bi {{ $i }}"></i></span>
                    <div>
                        <div class="stat-n">{{ $v }}</div>
                        <div class="fw-semibold small">{{ $l }}</div>
                        <div class="text-body-secondary" style="font-size:.75rem">{{ $sub }}</div>
                    </div>
                </a></div>
        @endforeach
    </div>

    @include('admin.traffic')

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="acard mb-4">
                <div class="card-header d-flex justify-content-between align-items-center border-0 pb-0 pt-3 px-4">
                    <h2 class="h6 mb-0">Recent messages</h2><a class="small" href="/admin/messages">View all</a>
                </div>
                <div class="mt-2">
                    @forelse($msgs as $m)
                        <div class="row-item"><span class="avatar">{{ strtoupper(mb_substr($m->name, 0, 1)) }}</span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold">{{ $m->name }} @if (!$m->is_read)
                                        <span class="badge text-bg-danger">new</span>
                                    @endif
                                </div>
                                <div class="small text-body-secondary text-truncate">{{ $m->subject }}</div>
                            </div><small
                                class="text-body-secondary text-nowrap">{{ $m->created_at->diffForHumans() }}</small>
                        </div>
                    @empty<div class="row-item text-body-secondary">No messages yet. Contact form messages will appear
                            here.</div>
                    @endforelse
                </div>
            </div>

            <div class="acard p-4">
                <h2 class="h6 mb-3">Manage content</h2>
                <div class="row g-2">
                    @foreach (config('admin') as $k => $R)
                        <div class="col-6 col-md-4"><a class="mini" href="/admin/{{ $k }}"><i
                                    class="bi {{ $ic[$k] ?? 'bi-dot' }}"></i>{{ $R['label'] }}<b>{{ $counts[$k] ?? 0 }}</b></a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="acard p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h2 class="h6 mb-0">Setup checklist</h2><span class="fw-bold text-primary">{{ $pct }}%</span>
                </div>
                <div class="progress mb-3" style="height:8px">
                    <div class="progress-bar"
                        style="width:{{ $pct }}%;background:linear-gradient(90deg,#4f46e5,#06b6d4)"></div>
                </div>
                @foreach ($check as [$l, $ok, $u])
                    <a class="check-i text-decoration-none {{ $ok ? 'text-body-secondary' : 'text-body' }}"
                        href="{{ $u }}"><i
                            class="bi {{ $ok ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary' }}"></i><span
                            class="{{ $ok ? 'text-decoration-line-through' : '' }}">{{ $l }}</span></a>
                @endforeach
            </div>
            <div class="acard">
                <div class="card-header d-flex justify-content-between align-items-center border-0 pb-0 pt-3 px-4">
                    <h2 class="h6 mb-0">Recent posts</h2><a class="small" href="/admin/posts">All</a>
                </div>
                <div class="mt-2">
                    @forelse($recent as $r)
                        <div class="row-item">
                            <div class="flex-grow-1 min-w-0 text-truncate"><a
                                    class="text-decoration-none fw-semibold text-body"
                                    href="/admin/posts/{{ $r->id }}/edit">{{ $r->title }}</a></div>
                            @if (!$r->published)
                            <span class="badge text-bg-secondary">draft</span>@else<span
                                    class="badge text-bg-success">live</span>
                            @endif
                        </div>
                    @empty<div class="row-item text-body-secondary">No posts yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
