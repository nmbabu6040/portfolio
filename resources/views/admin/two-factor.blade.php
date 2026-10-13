@extends('admin.base')
@section('c')
    <div class="text-center mb-4"><span class="sb-logo mx-auto mb-3" style="width:56px;height:56px;font-size:1.6rem"><i
                class="bi bi-shield-lock"></i></span>
        <h1 class="h4 mb-1">Two-factor login</h1>
        <p class="text-body-secondary small mb-0">Enter the 6-digit code from your authenticator app</p>
    </div>
    <form method="post" action="/admin/two-factor" class="acard login-card">@csrf
        <div class="mb-3"><label class="form-label">Code</label><input name="code"
                class="form-control form-control-lg text-center" inputmode="numeric" autocomplete="one-time-code" autofocus
                maxlength="20" placeholder="123456"></div>
        @error('code')
            <div class="text-danger small mb-3">{{ $message }}</div>
        @enderror
        <button class="btn btn-primary w-100 py-2">Verify</button>
        <div class="text-center mt-3 small"><a href="/admin/login">Back to login</a></div>
        <p class="small text-body-secondary mt-3 mb-0">Lost your phone? Type one of your recovery codes here instead.</p>
    </form>
@endsection
