@extends('admin.base')
@section('c')
    <div class="mb-4">
        <h1 class="h4 mb-0">Comments</h1>
        <p class="text-body-secondary small mb-0">Approve, reply to or remove blog comments.</p>
    </div>
    <ul class="nav nav-pills mb-3 gap-1">
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'spam' => 'Spam'] as $k => $l)
            <li class="nav-item"><a class="nav-link {{ $tab == $k ? 'active' : '' }}"
                    href="/admin/comments?tab={{ $k }}">{{ $l }} <span
                        class="badge text-bg-{{ $k == 'pending' && $counts[$k] ? 'warning' : 'secondary' }}">{{ $counts[$k] }}</span></a>
            </li>
        @endforeach
    </ul>
    @forelse($items as $c)
        <div class="acard p-4 mb-3">
            <div class="d-flex flex-wrap justify-content-between gap-2">
                <div><strong>{{ $c->name }}</strong>
                    @if ($c->is_admin)
                        <span class="badge text-bg-primary">Author</span>
                        @endif @if ($c->parent_id)
                            <span class="badge text-bg-light border">Reply</span>
                        @endif
                        <span class="small text-body-secondary">· <a
                                href="mailto:{{ $c->email }}">{{ $c->email }}</a></span>
                </div>
                <small class="text-body-secondary">{{ \Carbon\Carbon::parse($c->created_at)->format('d M Y, H:i') }}</small>
            </div>
            <div class="small text-body-secondary mb-2">On <a href="{{ url('/blog/' . $c->post_slug) }}" target="_blank"
                    rel="noopener">{{ $c->post_title }}</a></div>
            <div class="mb-3">{!! nl2br(e($c->body)) !!}</div>
            <div class="d-flex flex-wrap gap-2">
                @if ($c->status != 'approved')
                    <form method="post" action="/admin/comments/{{ $c->id }}/status">@csrf<input type="hidden"
                            name="status" value="approved"><button class="btn btn-sm btn-success"><i
                                class="bi bi-check2 me-1"></i>Approve</button></form>
                @endif
                @if ($c->status != 'spam')
                    <form method="post" action="/admin/comments/{{ $c->id }}/status">@csrf<input type="hidden"
                            name="status" value="spam"><button class="btn btn-sm btn-outline-warning"><i
                                class="bi bi-slash-circle me-1"></i>Spam</button></form>
                @endif
                <form method="post" action="/admin/comments/{{ $c->id }}"
                    onsubmit="return confirm('Delete this comment and its replies?')">@csrf @method('DELETE')<button
                        class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button></form>
            </div>
            <details class="mt-3">
                <summary class="small text-primary" style="cursor:pointer">Reply as {{ auth()->user()->name }}</summary>
                <form method="post" action="/admin/comments/{{ $c->id }}/reply" class="mt-2">@csrf
                    <textarea name="body" class="form-control mb-2" rows="3" required></textarea><button class="btn btn-sm btn-primary">Post reply (shown with an Author
                        badge)</button>
                </form>
            </details>
        </div>
    @empty
        <div class="acard p-5 text-center text-body-secondary">Nothing here.</div>
    @endforelse
    <div class="d-flex justify-content-center">{{ $items->links() }}</div>
@endsection
