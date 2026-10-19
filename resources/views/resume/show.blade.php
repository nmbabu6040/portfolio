@extends('layouts.app')
@use('App\Support\Ui')
@php
    $name = $s['name'] ?? $p['name'];
    $role = trim(str_replace('|', '·', $s['role'] ?? $p['role']));
    $summary = $s['resume_summary'] ?? Ui::plain($s['intro'] ?? $p['intro'], 700);
    $short = fn($u) => preg_replace('#^https?://(www\.)?#', '', rtrim((string) $u, '/'));
@endphp
@section('title', 'Resume | ' . $name)
@section('desc', 'Resume of ' . $name)
@push('head')
    <meta name="robots" content="noindex">
    <style>
        .paper {
            background: #fff;
            color: #1f2937;
            border-radius: 18px;
            box-shadow: var(--sh);
            padding: 44px
        }

        .paper h1 {
            font-size: 2rem;
            color: #111827;
            margin: 0
        }

        .paper .r-role {
            color: #4f46e5;
            font-weight: 600;
            margin: 2px 0 10px
        }

        .paper .r-contact {
            font-size: .9rem;
            color: #4b5563
        }

        .paper .r-contact span+span::before {
            content: " · ";
            color: #9ca3af
        }

        .paper .r-grid {
            display: grid;
            grid-template-columns: 1fr 2.1fr;
            gap: 34px;
            margin-top: 26px
        }

        .paper .r-h {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .14em;
            color: #4f46e5;
            font-weight: 700;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
            margin: 0 0 10px
        }

        .paper .r-sec {
            margin-bottom: 22px
        }

        .paper .r-item {
            margin-bottom: 12px;
            break-inside: avoid
        }

        .paper .r-top {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap
        }

        .paper .r-sub {
            color: #6b7280;
            font-size: .85rem
        }

        .paper p {
            margin: 0 0 .4rem
        }

        .paper a {
            color: inherit;
            text-decoration: none
        }

        @media(max-width:700px) {
            .paper {
                padding: 24px
            }

            .paper .r-grid {
                grid-template-columns: 1fr;
                gap: 10px
            }
        }

        @media print {
            @page {
                size: A4;
                margin: 12mm
            }

            .navbar,
            .foot-cta,
            footer.foot,
            #top,
            .wa,
            #cc,
            #prog,
            .mt-bar,
            .no-print,
            .alert {
                display: none !important
            }

            body,
            .sec {
                background: #fff !important
            }

            .sec {
                padding: 0 !important
            }

            .container {
                max-width: none !important;
                padding: 0 !important
            }

            .paper {
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 0 !important
            }
        }
    </style>
@endpush
@section('content')
    <section class="sec">
        <div class="container" style="max-width:920px">
            @if ($preview)
                <div class="alert alert-warning no-print">Preview only. The resume is not public yet. Turn on
                    "resume_enabled" in Profile &amp; Settings (Resume card) to publish it.</div>
            @endif
            <div class="d-flex flex-wrap gap-2 mb-4 no-print">
                <button type="button" class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print
                    / Save as PDF</button>
                @if (class_exists(\Dompdf\Dompdf::class))
                    <a class="btn btn-ghost" href="{{ url('/resume.pdf') }}" data-cta="cv"><i
                            class="bi bi-file-earmark-pdf me-1"></i>Download PDF</a>
                @endif
                <a class="btn btn-ghost" href="{{ url('/contact') }}">Contact me</a>
            </div>
            <article class="paper">
                <h1>{{ $name }}</h1>
                <div class="r-role">{{ $role }}</div>
                <div class="r-contact">
                    @if (!empty($s['email']))
                        <span>{{ $s['email'] }}</span>
                    @endif
                    @if (!empty($s['resume_phone']) && !empty($s['phone']))
                        <span>{{ $s['phone'] }}</span>
                    @endif
                    @if (!empty($s['resume_location']))
                        <span>{{ $s['resume_location'] }}</span>
                    @endif
                    <span>{{ $short(url('/')) }}</span>
                    @if (!empty($s['linkedin']))
                        <span>{{ $short($s['linkedin']) }}</span>
                    @endif
                    @if (!empty($s['github']))
                        <span>{{ $short($s['github']) }}</span>
                    @endif
                </div>
                <div class="r-grid">
                    <aside>
                        @if ($skills->count())
                            <div class="r-sec">
                                <h2 class="r-h">Skills</h2>
                                @foreach ($skills as $g)
                                    <div class="r-item"><b>{{ $g->title }}</b>
                                        <div class="r-sub">{{ implode(', ', Ui::list($g->items)) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if ($tech->count())
                            <div class="r-sec">
                                <h2 class="r-h">Technologies</h2>
                                @foreach ($tech as $cat => $list)
                                    <div class="r-item"><b>{{ $cat }}</b>
                                        <div class="r-sub">{{ $list->pluck('name')->implode(', ') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if ($certs->count())
                            <div class="r-sec">
                                <h2 class="r-h">Certifications</h2>
                                @foreach ($certs as $c)
                                    <div class="r-item"><b>{{ $c->title }}</b>
                                        <div class="r-sub">{{ trim($c->issuer . ' ' . $c->year) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </aside>
                    <main>
                        @if ($summary)
                            <div class="r-sec">
                                <h2 class="r-h">Summary</h2>
                                <p>{{ $summary }}</p>
                            </div>
                        @endif
                        @if ($exp->count())
                            <div class="r-sec">
                                <h2 class="r-h">Experience</h2>
                                @foreach ($exp as $e)
                                    <div class="r-item">
                                        <div class="r-top"><b>{{ $e->title }}</b><span
                                                class="r-sub">{{ $e->period }}</span></div>
                                        @if ($e->org)
                                            <div class="r-sub">{{ $e->org }}</div>
                                        @endif
                                        @if (Ui::list($e->areas))
                                            <div class="r-sub">{{ implode(' · ', Ui::list($e->areas)) }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if ($projects->count())
                            <div class="r-sec">
                                <h2 class="r-h">Selected projects</h2>
                                @foreach ($projects as $pr)
                                    <div class="r-item">
                                        <div class="r-top"><b>{{ $pr->title }}</b><span
                                                class="r-sub">{{ $pr->category }}</span></div>
                                        @if ($pr->tech)
                                            <div class="r-sub">{{ implode(', ', Ui::list($pr->tech)) }}</div>
                                        @endif
                                        <div>{{ Ui::plain($pr->description, 170) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if ($edu->count())
                            <div class="r-sec">
                                <h2 class="r-h">Education</h2>
                                @foreach ($edu as $e)
                                    <div class="r-item">
                                        <div class="r-top"><b>{{ $e->title }}</b><span
                                                class="r-sub">{{ $e->period }}</span></div>
                                        <div class="r-sub">{{ $e->institution }}</div>
                                        @if ($e->details)
                                            <div>{{ Ui::plain($e->details, 200) }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if ($ach->count())
                            <div class="r-sec">
                                <h2 class="r-h">Achievements</h2>
                                @foreach ($ach as $e)
                                    <div class="r-item">
                                        <div class="r-top"><b>{{ $e->title }}</b><span
                                                class="r-sub">{{ $e->year }}</span></div>
                                        @if ($e->details)
                                            <div>{{ Ui::plain($e->details, 200) }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </main>
                </div>
            </article>
        </div>
    </section>
@endsection
