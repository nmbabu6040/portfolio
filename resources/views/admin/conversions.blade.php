@extends('admin.base')
@section('c')
    @php
        $names = [
            'whatsapp' => 'WhatsApp',
            'phone' => 'Phone call',
            'email' => 'Email link',
            'quote' => 'Quote button',
            'book' => 'Book a call',
            'cv' => 'CV download',
        ];
        $pct = fn($a, $b) => $b > 0 ? round(($a / $b) * 100, 1) . '%' : '-';
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0">Conversions</h1>
            <p class="text-body-secondary small mb-0">From visitors to leads and won deals. Your own admin visits are not
                counted.</p>
        </div>
        <ul class="nav nav-pills gap-1">
            @foreach ([7, 30, 90] as $d)
                <li class="nav-item"><a class="nav-link {{ $days == $d ? 'active' : '' }}"
                        href="/admin/conversions?days={{ $d }}">{{ $d }} days</a></li>
            @endforeach
        </ul>
    </div>
    <div class="row g-3 mb-4">
        @foreach ([['Visitors', $visitors, null], ['Button clicks', $ctaTotal, $pct($ctaTotal, $visitors) . ' of visitors'], ['Leads', $leadCount, $pct($leadCount, $visitors) . ' of visitors'], ['Won', $wonCount, $pct($wonCount, $leadCount) . ' of leads']] as [$l, $v, $sub])
            <div class="col-6 col-xl-3">
                <div class="acard p-3 h-100">
                    <div class="small text-body-secondary">{{ $l }}</div>
                    <div class="stat-n">{{ number_format($v) }}</div>
                    @if ($sub)
                        <div class="small text-body-secondary">{{ $sub }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="acard p-4 h-100">
                <h2 class="h6 mb-3">Which button gets clicks</h2>
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Button</th>
                            <th class="text-end">Clicks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cta as $c)
                            <tr>
                                <td>{{ $names[$c->name] ?? $c->name }}</td>
                                <td class="text-end">{{ $c->c }}</td>
                        </tr>@empty<tr>
                                <td colspan="2" class="text-body-secondary">No clicks yet. Test in an incognito window.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <p class="small text-body-secondary mt-3 mb-0">A click means someone pressed the button. It does not mean
                    they sent a message.</p>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="acard p-4 h-100">
                <h2 class="h6 mb-3">Pages where buttons are clicked</h2>
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th class="text-end">Clicks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ctaPages as $p)
                            <tr>
                                <td class="text-truncate" style="max-width:260px">{{ $p->path == '/' ? 'Home' : $p->path }}
                                </td>
                                <td class="text-end">{{ $p->c }}</td>
                        </tr>@empty<tr>
                                <td colspan="2" class="text-body-secondary">No data yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="acard p-4 h-100">
                <h2 class="h6 mb-3">Leads by source</h2>
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th class="text-end">Leads</th>
                            <th class="text-end">Won</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sources as $s)
                            <tr>
                                <td>{{ $s->src }}</td>
                                <td class="text-end">{{ $s->c }}</td>
                                <td class="text-end">{{ $s->won }}</td>
                        </tr>@empty<tr>
                                <td colspan="3" class="text-body-secondary">No leads in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="acard p-4 h-100">
                <h2 class="h6 mb-3">First page of leads</h2>
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th class="text-end">Leads</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($landing as $p)
                            <tr>
                                <td class="text-truncate" style="max-width:260px">
                                    {{ $p->landing == '/' ? 'Home' : $p->landing }}</td>
                                <td class="text-end">{{ $p->c }}</td>
                        </tr>@empty<tr>
                                <td colspan="2" class="text-body-secondary">No data yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
