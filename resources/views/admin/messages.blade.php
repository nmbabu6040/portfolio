@extends('admin.base')
@section('c')
    <div class="mb-4">
        <h1 class="h4 mb-0">Messages &amp; quote requests</h1>
        <p class="text-body-secondary small mb-0">{{ $items->count() }} total</p>
    </div>
    @forelse($items as $m)
        <div class="acard mb-3 p-4 {{ $m->is_read ? '' : 'border-primary' }}">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <h2 class="h6 mb-1">{{ $m->subject }} @if ($m->kind == 'quote')
                        <span class="badge text-bg-info">Quote</span>
                        @endif @if (!$m->is_read)
                            <span class="badge text-bg-danger">new</span>
                        @endif
                </h2>
                <small class="text-body-secondary">{{ $m->created_at->format('Y-m-d H:i') }}</small>
            </div>
            <p class="small text-body-secondary mb-2">{{ $m->name }} · <a
                    href="mailto:{{ $m->email }}">{{ $m->email }}</a>
                @if ($m->phone)
                    · {{ $m->phone }}
                @endif
            </p>
            @if ($m->kind == 'quote')
                <div class="d-flex flex-wrap gap-2 mb-2"><span class="badge text-bg-light border">Service:
                        {{ $m->service }}</span>
                    @if ($m->budget)
                        <span class="badge text-bg-light border">Budget: {{ $m->budget }}</span>
                        @endif @if ($m->deadline)
                            <span class="badge text-bg-light border">Timeline: {{ $m->deadline }}</span>
                        @endif
                </div>
            @endif
            <p>{!! nl2br(e($m->message)) !!}</p>
            <div class="d-flex gap-2">
                @if (!$m->is_read)
                    <form method="post" action="/admin/messages/{{ $m->id }}/read">@csrf<button
                            class="btn btn-sm btn-outline-primary">Mark as read</button></form>
                @endif
                <a class="btn btn-sm btn-outline-secondary"
                    href="mailto:{{ $m->email }}?subject={{ urlencode('Re: ' . $m->subject) }}"><i
                        class="bi bi-reply me-1"></i>Reply</a>
                <form method="post" action="/admin/messages/{{ $m->id }}"
                    onsubmit="return confirm('Delete this message?')">@csrf @method('DELETE')<button
                        class="btn btn-sm btn-outline-danger">Delete</button></form>
            </div>
        </div>
    @empty<div class="acard p-5 text-center text-body-secondary">No messages yet.</div>
    @endforelse
@endsection
