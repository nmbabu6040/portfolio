@if ($traffic)
    @php
        $max = max(1, collect($traffic['series'])->max('views'));
        $tot = max(1, $traffic['total30']);
    @endphp
    <div class="acard p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h6 mb-0"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Visitors</h2>
            <span class="small text-body-secondary">Your own visits while logged in as admin are not counted</span>
        </div>
        <div class="row g-3 mb-4">
            @foreach ($traffic['cards'] as $c)
                <div class="col-md-4">
                    <div class="vcard">
                        <div class="small text-body-secondary mb-1">{{ $c['label'] }}</div>
                        <div class="d-flex align-items-baseline flex-wrap gap-2"><span
                                class="vnum">{{ number_format($c['views']) }}</span><span
                                class="small text-body-secondary me-2">views</span><span
                                class="vnum2">{{ number_format($c['visitors']) }}</span><span
                                class="small text-body-secondary">visitors</span></div>
                    </div>
                </div>
            @endforeach
        </div>
        @if ($traffic['total30'] == 0)
            <div class="text-body-secondary small py-3">No visits recorded yet. Open your website in a private
                (incognito) window to test. Visits made while you are logged in to the admin panel are not counted.
            </div>
        @else
            <div class="vchart" role="img" aria-label="Page views for the last 14 days">
                @foreach ($traffic['series'] as $d)
                    <div class="vbar"
                        title="{{ $d['label'] }}: {{ $d['views'] }} views, {{ $d['visitors'] }} visitors">
                        <div class="vbar-a">
                            <div class="vbar-v" style="height:{{ round(($d['views'] / $max) * 100) }}%"></div>
                        </div><small>{{ $d['short'] }}</small>
                    </div>
                @endforeach
            </div>
            <div class="small text-body-secondary mt-1 mb-4">Page views, last 14 days (hover a bar for details)</div>
            <div class="row g-4">
                <div class="col-lg-6">
                    <h3 class="h6">Top pages (30 days)</h3>
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Page</th>
                                <th class="text-end">Views</th>
                                <th class="text-end">Visitors</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($traffic['pages'] as $p)
                                <tr>
                                    <td class="text-truncate" style="max-width:240px">
                                        {{ $p->path === '/' ? 'Home' : $p->path }}</td>
                                    <td class="text-end">{{ $p->views }}</td>
                                    <td class="text-end">{{ $p->visitors }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <h3 class="h6">Where from (30 days)</h3>
                    <ul class="list-unstyled small mb-0">
                        <li class="d-flex justify-content-between py-1"><span>Direct /
                                unknown</span><b>{{ $traffic['direct'] }}</b></li>
                        @foreach ($traffic['refs'] as $r)
                            <li class="d-flex justify-content-between py-1 gap-2"><span
                                    class="text-truncate">{{ $r->referrer }}</span><b>{{ $r->views }}</b></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <h3 class="h6">Devices (30 days)</h3>
                    @foreach ($traffic['devices'] as $dv)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small"><span
                                    class="text-capitalize">{{ $dv->device }}</span><b>{{ round(($dv->views / $tot) * 100) }}%</b>
                            </div>
                            <div class="progress" style="height:6px">
                                <div class="progress-bar"
                                    style="width:{{ round(($dv->views / $tot) * 100) }}%;background:linear-gradient(90deg,#4f46e5,#06b6d4)">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
