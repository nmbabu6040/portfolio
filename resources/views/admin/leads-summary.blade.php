@if (!empty($leadStats))
    @php
        $L = $leadStats;
        $tiles = [
            'new' => ['New', 'primary'],
            'contacted' => ['Contacted', 'info'],
            'quote_sent' => ['Quote sent', 'warning'],
            'won' => ['Won', 'success'],
            'lost' => ['Lost', 'secondary'],
        ];
    @endphp
    <div class="acard p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h6 mb-0"><i class="bi bi-kanban me-2 text-primary"></i>Leads pipeline</h2>
            <div class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="/admin/leads">Open pipeline</a><a
                    class="btn btn-sm btn-outline-secondary" href="/admin/conversions">Conversions</a></div>
        </div>
        <div class="row g-2 mb-3">
            @foreach ($tiles as $k => [$l, $c])
                <div class="col"><a href="/admin/leads" class="vcard d-block text-decoration-none text-body">
                        <div class="small text-body-secondary"><span
                                class="badge text-bg-{{ $c }}">{{ $l }}</span></div>
                        <div class="vnum">{{ $L['stages'][$k]->c ?? 0 }}</div>
                        @if (!empty($L['stages'][$k]->v))
                            <div class="small text-body-secondary">{{ number_format($L['stages'][$k]->v) }}</div>
                        @endif
                    </a>
                </div>
            @endforeach
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="small text-body-secondary mb-1">Last 30 days</div>
                <div><b>{{ $L['leads30'] }}</b> leads from <b>{{ number_format($L['visitors30']) }}</b> visitors</div>
                <div class="small text-body-secondary">Conversion: {{ $L['rate'] === null ? '-' : $L['rate'] . '%' }}
                </div>
                @if ($L['wonMonth'])
                    <div class="small text-success mt-1">Won this month: {{ number_format($L['wonMonth']) }}</div>
                @endif
            </div>
            <div class="col-md-8">
                <div class="small text-body-secondary mb-1">Follow-ups due ({{ $L['dueCount'] }})</div>
                @forelse($L['due'] as $d)
                    <a class="d-flex justify-content-between small py-1 text-decoration-none text-body"
                        href="/admin/leads/{{ $d->id }}"><span
                            class="text-truncate">{{ $d->name }}</span><span
                            class="text-danger text-nowrap">{{ \Carbon\Carbon::parse($d->follow_up)->format('d M') }}</span></a>
                @empty<div class="small text-body-secondary">Nothing is due. Set a follow-up date on a lead to see
                        it here.</div>
                @endforelse
            </div>
        </div>
    </div>
@endif
