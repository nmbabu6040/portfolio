@extends('admin.base')
@section('c')
    @php
        $me = auth()->user();
        $rank = config('roles.rank');
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0">Users &amp; roles</h1>
            <p class="text-body-secondary small mb-0">{{ $items->count() }} user(s)</p>
        </div>
        <a class="btn btn-primary" href="/admin/users/create"><i class="bi bi-plus-lg me-1"></i>Add user</a>
    </div>
    <div class="acard mb-4">
        @foreach ($items as $u)
            @php $manage = $me->role === 'super_admin' || ($rank[$u->role] ?? 0) < $rank[$me->role]; @endphp
            <div class="row-item">
                <span class="avatar">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate">{{ $u->name }}
                        <span class="badge text-bg-primary">{{ config('roles.labels')[$u->role] ?? $u->role }}</span>
                        @if (!$u->active)
                            <span class="badge text-bg-secondary">inactive</span>
                        @endif
                        @if ($u->two_factor_enabled)
                            <span class="badge text-bg-success">2FA</span>
                        @endif
                        @if ($u->id === $me->id)
                            <span class="badge text-bg-light border">you</span>
                        @endif
                    </div>
                    <div class="small text-body-secondary text-truncate">{{ $u->email }}</div>
                </div>
                @if ($manage)
                    <div class="d-flex gap-2 flex-shrink-0"><a class="icon-btn" href="/admin/users/{{ $u->id }}/edit"
                            title="Edit"><i class="bi bi-pencil"></i></a>
                        @if ($u->id !== $me->id)
                            <form method="post" action="/admin/users/{{ $u->id }}"
                                onsubmit="return confirm('Delete this user?')">@csrf @method('DELETE')<button
                                    class="icon-btn del" title="Delete"><i class="bi bi-trash"></i></button></form>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    <div class="acard p-4">
        <h2 class="h6 mb-3">What each role can do</h2>
        @foreach (config('roles.labels') as $k => $l)
            <div class="d-flex gap-3 py-2 border-bottom"><span class="badge text-bg-primary align-self-start"
                    style="min-width:96px">{{ $l }}</span><span
                    class="small text-body-secondary">{{ config('roles.desc')[$k] }}</span></div>
        @endforeach
    </div>
@endsection
