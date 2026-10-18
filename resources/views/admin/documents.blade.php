@extends('admin.base')
@section('c')
    @php
        $sum = fn($d) => round((($d->sub ?? 0) - $d->discount) * (1 + $d->tax_percent / 100), 2);
        $badge = [
            'draft' => 'secondary',
            'sent' => 'info',
            'accepted' => 'success',
            'paid' => 'success',
            'cancelled' => 'danger',
        ];
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0">Quotes &amp; invoices</h1>
            <p class="text-body-secondary small mb-0">Create PDF quotations and invoices for your clients</p>
        </div>
        <div class="d-flex gap-2"><a class="btn btn-primary" href="/admin/documents/create?type=quotation"><i
                    class="bi bi-plus-lg me-1"></i>Quotation</a><a class="btn btn-outline-primary"
                href="/admin/documents/create?type=invoice"><i class="bi bi-plus-lg me-1"></i>Invoice</a></div>
    </div>
    <ul class="nav nav-pills mb-3 gap-1">
        @foreach ([null => 'All', 'quotation' => 'Quotations', 'invoice' => 'Invoices'] as $k => $l)
            <li class="nav-item"><a class="nav-link {{ $type == $k ? 'active' : '' }}"
                    href="/admin/documents{{ $k ? '?type=' . $k : '' }}">{{ $l }}</a></li>
        @endforeach
    </ul>
    <div class="acard">
        @forelse($items as $d)
            <div class="row-item">
                <span class="thumb"><i class="bi bi-file-earmark-text"></i></span>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate"><a class="text-decoration-none text-body"
                            href="/admin/documents/{{ $d->id }}/edit">{{ $d->number }}</a> · {{ $d->client_name }}
                        <span class="badge text-bg-{{ $badge[$d->status] ?? 'secondary' }}">{{ $d->status }}</span></div>
                    <div class="small text-body-secondary">{{ \Carbon\Carbon::parse($d->issue_date)->format('d M Y') }}
                    </div>
                </div>
                <div class="fw-semibold text-nowrap">{{ $d->currency }} {{ number_format($sum($d), 2) }}</div>
                <div class="d-flex gap-2 flex-shrink-0"><a class="icon-btn" href="/admin/documents/{{ $d->id }}/pdf"
                        target="_blank" title="Open PDF"><i class="bi bi-file-earmark-pdf"></i></a><a class="icon-btn"
                        href="/admin/documents/{{ $d->id }}/edit" title="Edit"><i class="bi bi-pencil"></i></a>
                </div>
            </div>
        @empty
            <div class="row-item text-body-secondary py-5 justify-content-center">Nothing here yet. Create a quotation or an
                invoice.</div>
        @endforelse
    </div>
    <div class="mt-3 d-flex justify-content-center">{{ $items->links() }}</div>
@endsection
