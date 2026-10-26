@extends('admin.base')
@section('c')
    @php $lg = \App\Models\Setting::map()['logo'] ?? null; @endphp
    <div class="text-center mb-4">
        @if ($lg)
            <div class="mb-3"><span class="sb-wide" style="padding:10px 16px"><img src="{{ asset($lg) }}" alt=""
                    style="height:52px;max-width:240px"></span></div>@else<span class="sb-logo mx-auto mb-3"
                style="width:56px;height:56px;font-size:1.6rem"><i class="bi bi-speedometer2"></i></span>
        @endif
        <h1 class="h4 mb-1">Admin login</h1>
        <p class="text-body-secondary small mb-0">Sign in to manage your portfolio</p>
    </div>
    <form method="post" action="/admin/login" class="acard login-card">@csrf
        <div class="mb-3"><label class="form-label">Email</label><input name="email" type="email" class="form-control"
                value="{{ old('email') }}" autocomplete="username" autofocus></div>
        <div class="mb-3"><label class="form-label">Password</label><input name="password" type="password"
                class="form-control" autocomplete="current-password"></div>
        @error('email')
            <div class="text-danger small mb-3">{{ $message }}</div>
        @enderror
        <button class="btn btn-primary w-100 py-2">Sign in</button>
    </form>
@endsection
