@extends('admin.base')
@section('c')
    @php
        $me = auth()->user();
        $h = now()->hour;
        $hello = $h < 12 ? 'Good morning' : ($h < 17 ? 'Good afternoon' : 'Good evening');
        $first = explode(' ', trim($s['name'] ?? $me->name))[0];
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
        $badge = [
            'created' => 'success',
            'updated' => 'primary',
            'deleted' => 'danger',
            'login' => 'info',
            'logout' => 'secondary',
            'failed' => 'warning',
            'backup' => 'dark',
        ];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $hello }}, {{ $first }} 👋</h1>
            <p class="text-body-secondary mb-0">{{ now()->format('l, d F Y') }} · Here is what is happening on your site.</p>
        </div>
        <div class="d-flex flex-wrap gap-2"><a class="btn btn-primary" href="/admin/projects/create"><i
                    class="bi bi-plus-lg me-1"></i>Project</a><a class="btn btn-primary" href="/admin/posts/create"><i
                    class="bi bi-pencil-square me-1"></i>Write post</a><a class="btn btn-outline-secondary" href="/"
                target="_blank"><i class="bi bi-box-arrow-up-right me-1"></i>View site</a></div>
    </div>

    <div class="row g-3 mb-4">
        @foreach ([['Inbox', $kpi['unread'], 'bi-envelope', 'g3', '/admin/messages', $kpi['unread'] == 1 ? 'unread message' : 'unread messages', $kpi['quotes'] . ' quote request(s) · ' . $kpi['messages'] . ' total'], ['Subscribers', $kpi['subs'], 'bi-people', 'g2', '/admin/subscribers', 'active', '+' . $kpi['subsNew'] . ' this week'], ['Blog posts', $kpi['posts'], 'bi-journal-text', 'g1', '/admin/posts', 'live', $kpi['drafts'] . ' draft(s)'], ['Projects', $counts['projects'] ?? 0, 'bi-kanban', 'g4', '/admin/projects', 'published', ($counts['services'] ?? 0) . ' services · ' . ($counts['faqs'] ?? 0) . ' FAQs']] as [$l, $v, $i, $g, $u, $unit, $sub])
            <div class="col-6 col-xl-3"><a href="{{ $u }}" class="acard stat h-100"><span
                        class="stat-i {{ $g }}"><i class="bi {{ $i }}"></i></span>
                    <div>
                        <div class="d-flex align-items-baseline gap-2"><span class="stat-n">{{ $v }}</span><span
                                class="small text-body-secondary">{{ $unit }}</span></div>
                        <div class="fw-semibold small">{{ $l }}</div>
                        <div class="text-body-secondary" style="font-size:.75rem">{{ $sub }}</div>
                    </div>
                </a></div>
        @endforeach
    </div>

    @include('admin.leads-summary')
    @include('admin.traffic')

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="acard h-100">
                <div class="card-header border-0 pb-0 pt-3 px-4">
                    <h2 class="h6 mb-0"><i class="bi bi-exclamation-circle me-2 text-warning"></i>Needs attention</h2>
                </div>
                <div class="mt-2">
                    @forelse($attention as [$label, $n, $url, $icon])
                        <a class="row-item text-decoration-none text-body" href="{{ $url }}"><i
                                class="bi {{ $icon }} text-primary"></i><span
                                class="flex-grow-1">{{ $label }}</span>
                            @if ($n > 1)
                                <span class="badge text-bg-warning">{{ $n }}</span>
                            @endif
                            <i class="bi bi-chevron-right text-body-secondary small">
                            </i>
                        </a>
                    @empty
                        <div class="row-item text-success"><i class="bi bi-check-circle-fill"></i>Everything looks good.
                            Nothing needs your attention.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="acard h-100">
                <div class="card-header border-0 pb-0 pt-3 px-4 d-flex justify-content-between">
                    <h2 class="h6 mb-0"><i class="bi bi-fire me-2 text-danger"></i>Top blog posts (30 days)</h2><a
                        class="small" href="/admin/posts">All posts</a>
                </div>
                <div class="mt-2">
                    @forelse($topPosts as $i => $p)
                        <div class="row-item"><span class="fw-bold text-body-secondary"
                                style="width:18px">{{ $i + 1 }}</span><span
                                class="flex-grow-1 text-truncate">{{ $p['title'] }}</span><span
                                class="small text-body-secondary text-nowrap">{{ $p['views'] }} views ·
                                {{ $p['visitors'] }} visitors</span></div>
                    @empty
                        <div class="row-item text-body-secondary">No blog visits yet. They show here once people read your
                            posts.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        @if ($activity->count())
            <div class="col-xl-4">
                <div class="acard h-100">
                    <div class="card-header border-0 pb-0 pt-3 px-4 d-flex justify-content-between">
                        <h2 class="h6 mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Recent activity</h2><a
                            class="small" href="/admin/activity">All</a>
                    </div>
                    <div class="mt-2">
                        @foreach ($activity as $a)
                            <div class="row-item"><span class="badge text-bg-{{ $badge[$a->action] ?? 'secondary' }}"
                                    style="min-width:64px">{{ $a->action }}</span>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="small text-truncate">{{ $a->summary }}</div>
                                    <div class="text-body-secondary" style="font-size:.72rem">{{ $a->user_name }} ·
                                        {{ \Carbon\Carbon::parse($a->created_at)->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        <div class="col-xl-{{ $activity->count() ? 4 : 6 }}">
            <div class="acard h-100">
                <div class="card-header border-0 pb-0 pt-3 px-4 d-flex justify-content-between">
                    <h2 class="h6 mb-0"><i class="bi bi-receipt me-2 text-info"></i>Quote requests &amp; messages</h2><a
                        class="small" href="/admin/messages">All</a>
                </div>
                <div class="mt-2">
                    @foreach ($quotes as $m)
                        <div class="row-item"><span class="avatar">{{ strtoupper(mb_substr($m->name, 0, 1)) }}</span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold small">{{ $m->name }} <span
                                        class="badge text-bg-info">Quote</span>
                                    @if (!$m->is_read)
                                        <span class="badge text-bg-danger">new</span>
                                    @endif
                                </div>
                                <div class="small text-body-secondary text-truncate">{{ $m->service }}@if ($m->budget)
                                        · {{ $m->budget }}
                                    @endif
                                </div>
                            </div><small
                                class="text-body-secondary text-nowrap">{{ $m->created_at->diffForHumans(null, true) }}</small>
                        </div>
                    @endforeach
                    @foreach ($msgs as $m)
                        <div class="row-item"><span class="avatar">{{ strtoupper(mb_substr($m->name, 0, 1)) }}</span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold small">{{ $m->name }}@if (!$m->is_read)
                                        <span class="badge text-bg-danger">new</span>
                                    @endif
                                </div>
                                <div class="small text-body-secondary text-truncate">{{ $m->subject }}</div>
                            </div><small
                                class="text-body-secondary text-nowrap">{{ $m->created_at->diffForHumans(null, true) }}</small>
                        </div>
                    @endforeach
                    @if (!$quotes->count() && !$msgs->count())
                        <div class="row-item text-body-secondary">No messages yet.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-{{ $activity->count() ? 4 : 6 }}">
            <div class="acard h-100">
                <div class="card-header border-0 pb-0 pt-3 px-4 d-flex justify-content-between">
                    <h2 class="h6 mb-0"><i class="bi bi-people me-2 text-success"></i>Newest subscribers</h2><a
                        class="small" href="/admin/subscribers">All</a>
                </div>
                <div class="mt-2">
                    @forelse($subsList as $sb)
                        <div class="row-item"><i class="bi bi-envelope text-body-secondary"></i><span
                                class="flex-grow-1 text-truncate small">{{ $sb->email }}</span><small
                                class="text-body-secondary text-nowrap">{{ $sb->created_at->diffForHumans(null, true) }}</small>
                        </div>
                    @empty<div class="row-item text-body-secondary">No subscribers yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="acard p-4">
        <h2 class="h6 mb-3">Manage content</h2>
        <div class="row g-2">
            @foreach (config('admin') as $k => $R)
                <div class="col-6 col-md-4 col-xl-3"><a class="mini" href="/admin/{{ $k }}"><i
                            class="bi {{ $ic[$k] ?? 'bi-dot' }}"></i>{{ $R['label'] }}<b>{{ $counts[$k] ?? 0 }}</b></a>
                </div>
            @endforeach
        </div>
    </div>
@endsection
