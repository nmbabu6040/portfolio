@extends('admin.base')
@section('c')
    @php
        $stages = [
            'new' => ['New', 'primary'],
            'contacted' => ['Contacted', 'info'],
            'quote_sent' => ['Quote sent', 'warning'],
            'won' => ['Won', 'success'],
            'lost' => ['Lost', 'secondary'],
        ];
        $today = now()->toDateString();
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0">Leads pipeline</h1>
            <p class="text-body-secondary small mb-0">Drag a card to another column to change its stage. Click a name to open
                the lead.</p>
        </div>
        <div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="/admin/conversions"><i
                    class="bi bi-bullseye me-1"></i>Conversions</a><a class="btn btn-outline-secondary"
                href="/admin/messages">All messages</a></div>
    </div>
    <div class="board">
        @foreach ($stages as $key => [$label, $color])
            @php $col = $groups[$key] ?? collect(); @endphp
            <div class="bcol">
                <div class="bcol-h"><span class="badge text-bg-{{ $color }}">{{ $label }}</span><span
                        class="small text-body-secondary"><span data-count>{{ $col->count() }}</span>
                        @if ($col->sum('deal_value'))
                            · {{ number_format($col->sum('deal_value')) }}
                        @endif
                    </span></div>
                <div class="bcol-b" data-stage="{{ $key }}">
                    @foreach ($col as $m)
                        <div class="lcard" data-id="{{ $m->id }}">
                            <div class="d-flex justify-content-between gap-2"><a class="fw-semibold text-truncate"
                                    href="/admin/leads/{{ $m->id }}">{{ $m->name }}</a>
                                @if (!$m->is_read)
                                    <span class="badge text-bg-danger">new</span>
                                @endif
                            </div>
                            <div class="small text-body-secondary text-truncate">
                                {{ $m->kind == 'quote' ? ($m->service ?: $m->subject) : $m->subject }}</div>
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                <span
                                    class="badge text-bg-light border">{{ $m->kind == 'quote' ? 'Quote' : 'Contact' }}</span>
                                @if ($m->source)
                                    <span class="badge text-bg-light border">{{ $m->source }}</span>
                                @endif
                                @if ($m->deal_value)
                                    <span class="badge text-bg-success">{{ number_format($m->deal_value) }}</span>
                                @endif
                                @if ($m->follow_up && !in_array($key, ['won', 'lost']))
                                    <span class="badge text-bg-{{ $m->follow_up <= $today ? 'danger' : 'secondary' }}"
                                        title="Follow-up">{{ \Carbon\Carbon::parse($m->follow_up)->format('d M') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endsection
