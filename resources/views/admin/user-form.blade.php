@extends('admin.base')
@section('c')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h4 mb-0">{{ $u ? 'Edit user' : 'Add user' }}</h1>
        <a class="btn btn-outline-secondary" href="/admin/users"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <form method="post" class="acard p-4" style="max-width:640px" action="/admin/users{{ $u ? '/' . $u->id : '' }}"
        autocomplete="off">@csrf
        <div class="mb-3"><label class="form-label fw-semibold">Name</label><input name="name" class="form-control"
                value="{{ old('name', $u->name ?? '') }}" required></div>
        <div class="mb-3"><label class="form-label fw-semibold">Email (used to log in)</label><input type="email"
                name="email" class="form-control" value="{{ old('email', $u->email ?? '') }}" required></div>
        <div class="mb-3"><label class="form-label fw-semibold">Role</label>
            <select name="role" class="form-select" @if ($u && $u->id === auth()->id()) disabled @endif>
                @foreach ($roles as $k => $l)
                    <option value="{{ $k }}" @selected(old('role', $u->role ?? 'author') == $k)>{{ $l }}</option>
                @endforeach
            </select>
            @if ($u && $u->id === auth()->id())
                <div class="form-text">You cannot change your own role.</div>
            @endif
            <div class="form-text">Roles you can give are limited to your own level or below.</div>
        </div>
        <div class="mb-3"><label
                class="form-label fw-semibold">{{ $u ? 'New password (leave empty to keep the current one)' : 'Password' }}</label>
            <input type="password" name="password" class="form-control" minlength="10" autocomplete="new-password"
                {{ $u ? '' : 'required' }}>
            <div class="form-text">At least 10 characters. Give the password to the person safely, and ask them to change it
                in Account &gt; Password.</div>
        </div>
        @if (!$u || $u->id !== auth()->id())
            <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="active"
                    value="1" id="act" @checked(old('active', $u->active ?? true))><label class="form-check-label"
                    for="act">Account is active</label></div>
        @endif
        @if ($u && $u->two_factor_enabled)
            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="reset_2fa" value="1"
                    id="r2"><label class="form-check-label" for="r2">Turn off this user's two-factor login (use
                    if they lost their phone)</label></div>
        @endif
        <button class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save</button> <a
            class="btn btn-outline-secondary" href="/admin/users">Cancel</a>
    </form>
@endsection
