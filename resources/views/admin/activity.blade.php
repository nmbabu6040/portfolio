@extends('admin.base')
@section('c')
    @php
        $badge = [
            'created' => 'success',
            'updated' => 'primary',
            'deleted' => 'danger',
            'login' => 'info',
            'logout' => 'secondary',
            'failed' => 'warning',
            'backup' => 'dark',
        ];
    @endphp
    <div class="mb-4">
        <h1 class="h4 mb-0">Activity log</h1>
        <p class="text-body-secondary small mb-0">Who changed what and when. Entries are kept for 180 days.</p>
    </div>
    <form method="get" class="d-flex flex-wrap gap-2 mb-3">
        <select name="user" class="form-select" style="max-width:220px" onchange="this.form.submit()">
            <option value="">All users</option>
            @foreach ($users as $u)
                <option @selected(request('user') == $u)>{{ $u }}</option>
            @endforeach
        </select>
        <select name="action" class="form-select" style="max-width:180px" onchange="this.form.submit()">
            <option value="">All actions</option>
            @foreach ($actions as $a)
                <option @selected(request('action') == $a)>{{ $a }}</option>
            @endforeach
        </select>
        @if (request()->hasAny(['user', 'action']))
            <a class="btn btn-outline-secondary" href="/admin/activity">Clear</a>
        @endif
    </form>
    <div class="acard">
        @forelse($items as $i)
            <div class="row-item">
                <span class="badge text-bg-{{ $badge[$i->action] ?? 'secondary' }}"
                    style="min-width:74px">{{ $i->action }}</span>
                <div class="flex-grow-1 min-w-0">
                    <div class="text-truncate">{{ $i->summary }}</div>
                    <div class="small text-body-secondary">{{ $i->user_name }} · {{ $i->ip }}</div>
                </div>
                <small
                    class="text-body-secondary text-nowrap">{{ \Carbon\Carbon::parse($i->created_at)->format('d M, H:i') }}</small>
            </div>
        @empty
            <div class="row-item text-body-secondary py-5 justify-content-center">No activity yet.</div>
        @endforelse
    </div>
    <div class="mt-3 d-flex justify-content-center">{{ $items->links() }}</div>
@endsection
