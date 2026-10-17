@extends('admin.base')
@section('c')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h4 mb-0">{{ $m->name }}</h1>
        <a class="btn btn-outline-secondary" href="/admin/leads"><i class="bi bi-arrow-left me-1"></i>Pipeline</a>
    </div>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="acard p-4">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span
                        class="badge text-bg-{{ $m->kind == 'quote' ? 'info' : 'secondary' }}">{{ $m->kind == 'quote' ? 'Quote request' : 'Contact message' }}</span>
                    <span
                        class="small text-body-secondary">{{ \Carbon\Carbon::parse($m->created_at)->format('d M Y, H:i') }}</span>
                </div>
                <p class="mb-1"><i class="bi bi-envelope me-2 text-primary"></i><a
                        href="mailto:{{ $m->email }}">{{ $m->email }}</a></p>
                @if ($m->phone)
                    <p class="mb-3"><i class="bi bi-telephone me-2 text-primary"></i><a
                            href="tel:{{ preg_replace('/[^\d+]/', '', $m->phone) }}">{{ $m->phone }}</a></p>
                @endif
                @if ($m->kind == 'quote')
                    <div class="d-flex flex-wrap gap-2 mb-3"><span class="badge text-bg-light border">Service:
                            {{ $m->service }}</span>
                        @if ($m->budget)
                            <span class="badge text-bg-light border">Budget: {{ $m->budget }}</span>
                            @endif @if ($m->deadline)
                                <span class="badge text-bg-light border">Timeline: {{ $m->deadline }}</span>
                            @endif
                    </div>
                @endif
                <h2 class="h6">{{ $m->subject }}</h2>
                <div class="mb-4">{!! nl2br(e($m->message)) !!}</div>
                <a class="btn btn-primary"
                    href="mailto:{{ $m->email }}?subject={{ urlencode('Re: ' . $m->subject) }}"><i
                        class="bi bi-reply me-1"></i>Reply by email</a>
                <hr>
                <h2 class="h6">Where this lead came from</h2>
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <th style="width:140px">Source</th>
                            <td>{{ $m->source ?: 'Not recorded' }}</td>
                        </tr>
                        <tr>
                            <th>First page</th>
                            <td>{{ $m->landing ?: 'Not recorded' }}</td>
                        </tr>
                        @if ($m->campaign)
                            <tr>
                                <th>Campaign</th>
                                <td>{{ $m->campaign }}</td>
                            </tr>
                        @endif
                        @if ($m->referrer)
                            <tr>
                                <th>Referrer</th>
                                <td>{{ $m->referrer }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-5">
            <form method="post" action="/admin/leads/{{ $m->id }}" class="acard p-4">@csrf
                <h2 class="h6 mb-3">Pipeline</h2>
                <div class="mb-3"><label class="form-label fw-semibold">Stage</label><select name="stage"
                        class="form-select">
                        @foreach ($stages as $k => $l)
                            <option value="{{ $k }}" @selected(old('stage', $m->stage) == $k)>{{ $l }}</option>
                        @endforeach
                    </select></div>
                <div class="mb-3"><label class="form-label fw-semibold">Deal value</label><input type="number"
                        min="0" name="deal_value" class="form-control"
                        value="{{ old('deal_value', $m->deal_value) }}">
                    <div class="form-text">Numbers only, in the currency you quote.</div>
                </div>
                <div class="mb-3"><label class="form-label fw-semibold">Follow-up date</label><input type="date"
                        name="follow_up" class="form-control" value="{{ old('follow_up', $m->follow_up) }}">
                    <div class="form-text">Shows on the dashboard when it is due. Clear it when done.</div>
                </div>
                <div class="mb-3"><label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" rows="6" class="form-control">{{ old('notes', $m->notes) }}</textarea>
                </div>
                <button class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save</button>
            </form>
        </div>
    </div>
@endsection
