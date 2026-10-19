<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 28px 34px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            color: #1f2937;
            line-height: 1.45;
        }

        h1 {
            font-size: 24px;
            margin: 0;
            color: #111827;
        }

        h2 {
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #4f46e5;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 3px;
            margin: 14px 0 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
        }

        .role {
            color: #4f46e5;
            font-size: 12px;
            margin: 2px 0 6px;
        }

        .muted {
            color: #6b7280;
        }

        .item {
            margin-bottom: 8px;
            page-break-inside: avoid;
        }

        .r {
            text-align: right;
        }
    </style>
</head>

<body>
    @php
        $name = $s['name'] ?? $p['name'];
        $role = trim(str_replace('|', ' - ', $s['role'] ?? $p['role']));
        $summary = $s['resume_summary'] ?? \App\Support\Ui::plain($s['intro'] ?? $p['intro'], 700);
        $short = fn($u) => preg_replace('#^https?://(www\.)?#', '', rtrim((string) $u, '/'));
        $bits = array_filter([
            $s['email'] ?? null,
            !empty($s['resume_phone']) ? $s['phone'] ?? null : null,
            $s['resume_location'] ?? null,
            $short(url('/')),
            !empty($s['linkedin']) ? $short($s['linkedin']) : null,
            !empty($s['github']) ? $short($s['github']) : null,
        ]);
    @endphp
    <h1>{{ $name }}</h1>
    <div class="role">{{ $role }}</div>
    <div class="muted">{{ implode('   |   ', $bits) }}</div>
    <table style="margin-top:12px">
        <tr>
            <td style="width:30%;padding-right:18px">
                @if ($skills->count())
                    <h2>Skills</h2>
                    @foreach ($skills as $g)
                        <div class="item"><b>{{ $g->title }}</b><br><span
                                class="muted">{{ implode(', ', \App\Support\Ui::list($g->items)) }}</span></div>
                    @endforeach
                @endif
                @if ($tech->count())
                    <h2>Technologies</h2>
                    @foreach ($tech as $cat => $list)
                        <div class="item"><b>{{ $cat }}</b><br><span
                                class="muted">{{ $list->pluck('name')->implode(', ') }}</span></div>
                    @endforeach
                @endif
                @if ($certs->count())
                    <h2>Certifications</h2>
                    @foreach ($certs as $c)
                        <div class="item"><b>{{ $c->title }}</b><br><span
                                class="muted">{{ trim($c->issuer . ' ' . $c->year) }}</span></div>
                    @endforeach
                @endif
            </td>
            <td>
                @if ($summary)
                    <h2 style="margin-top:0">Summary</h2>
                    <p style="margin:0">{{ $summary }}</p>
                @endif
                @if ($exp->count())
                    <h2>Experience</h2>
                    @foreach ($exp as $e)
                        <div class="item">
                            <table>
                                <tr>
                                    <td><b>{{ $e->title }}</b></td>
                                    <td class="r muted">{{ $e->period }}</td>
                                </tr>
                            </table>
                            @if ($e->org)
                                <span class="muted">{{ $e->org }}</span><br>
                            @endif
                            @if (\App\Support\Ui::list($e->areas))
                                <span class="muted">{{ implode(' | ', \App\Support\Ui::list($e->areas)) }}</span>
                            @endif
                        </div>
                    @endforeach
                @endif
                @if ($projects->count())
                    <h2>Selected projects</h2>
                    @foreach ($projects as $pr)
                        <div class="item">
                            <table>
                                <tr>
                                    <td><b>{{ $pr->title }}</b></td>
                                    <td class="r muted">{{ $pr->category }}</td>
                                </tr>
                            </table>
                            @if ($pr->tech)
                                <span class="muted">{{ implode(', ', \App\Support\Ui::list($pr->tech)) }}</span><br>
                            @endif
                            {{ \App\Support\Ui::plain($pr->description, 170) }}
                        </div>
                    @endforeach
                @endif
                @if ($edu->count())
                    <h2>Education</h2>
                    @foreach ($edu as $e)
                        <div class="item">
                            <table>
                                <tr>
                                    <td><b>{{ $e->title }}</b></td>
                                    <td class="r muted">{{ $e->period }}</td>
                                </tr>
                            </table>
                            <span class="muted">{{ $e->institution }}</span>
                            @if ($e->details)
                                <br>{{ \App\Support\Ui::plain($e->details, 200) }}
                            @endif
                        </div>
                    @endforeach
                @endif
                @if ($ach->count())
                    <h2>Achievements</h2>
                    @foreach ($ach as $e)
                        <div class="item">
                            <table>
                                <tr>
                                    <td><b>{{ $e->title }}</b></td>
                                    <td class="r muted">{{ $e->year }}</td>
                                </tr>
                            </table>
                            @if ($e->details)
                                {{ \App\Support\Ui::plain($e->details, 200) }}
                            @endif
                        </div>
                    @endforeach
                @endif
            </td>
        </tr>
    </table>
</body>

</html>
