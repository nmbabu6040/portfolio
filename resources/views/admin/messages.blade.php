@extends('admin.base')
@section('c')
    <h1 class="h3 mb-3">
        Messages</h1>
    @forelse($items as $m)
        <div class="card mb-3 {{ $m->is_read ? '' : 'border-primary' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                    <h2 class="h6 mb-1">{{ $m->subject }} @if (!$m->is_read)
                            <span class="badge text-bg-danger">new</span>
                        @endif
                    </h2><small class="text-body-secondary">{{ $m->created_at->format('Y-m-d H:i') }}</small>
                </div>
                <p class="small text-body-secondary mb-2">{{ $m->name }} · <a
                        href="mailto:{{ $m->email }}">{{ $m->email }}</a>
                    @if ($m->phone)
                        · {{ $m->phone }}
                    @endif
                </p>
                <p>{!! nl2br(e($m->message)) !!}</p>
                <div class="d-flex gap-2">
                    @if (!$m->is_read)
                        <form method="post" action="/admin/messages/{{ $m->id }}/read">@csrf<button
                                class="btn btn-sm btn-outline-primary">Mark as read</button></form>
                    @endif
                    <form method="post" action="/admin/messages/{{ $m->id }}"
                        onsubmit="return confirm('Delete this message?')">@csrf @method('DELETE')<button
                            class="btn btn-sm btn-outline-danger">Delete</button></form>
                </div>
            </div>
        </div>
    @empty<p class="text-body-secondary">No messages yet.</p>
    @endforelse
@endsection
