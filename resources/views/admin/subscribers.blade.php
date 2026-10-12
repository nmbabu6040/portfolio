@extends('admin.base')
@section('c')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0">Subscribers</h1>
            <p class="text-body-secondary small mb-0">{{ $active }} active · {{ $items->count() }} total</p>
        </div>
        <a class="btn btn-primary" href="/admin/subscribers/export"><i class="bi bi-download me-1"></i>Export CSV</a>
    </div>
    <div class="alert alert-info small">This list collects the emails. To send a newsletter, export the CSV and import it
        into an email service such as Brevo or Mailchimp. Only email people who are <b>active</b>, and always include an
        unsubscribe link (each person's link is in the CSV).</div>
    <div class="acard">
        @forelse($items as $r)
            <div class="row-item">
                <span class="thumb"><i class="bi bi-envelope"></i></span>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate">{{ $r->email }} @if ($r->status == 'active')
                        <span class="badge text-bg-success">active</span>@else<span
                                class="badge text-bg-secondary">unsubscribed</span>
                        @endif
                    </div>
                    <div class="small text-body-secondary">{{ $r->created_at->format('d M Y, H:i') }}</div>
                </div>
                <form method="post" action="/admin/subscribers/{{ $r->id }}"
                    onsubmit="return confirm('Delete this subscriber?')">@csrf @method('DELETE')<button class="icon-btn del"
                        title="Delete"><i class="bi bi-trash"></i></button></form>
            </div>
        @empty<div class="row-item text-body-secondary py-5 justify-content-center">No subscribers yet.</div>
        @endforelse
    </div>
@endsection
