@extends('admin.base')
@section('c')
    <h1 class="h3 mb-3">
        Change password</h1>
    <form method="post" action="/admin/password" class="card p-4" style="max-width:480px">@csrf
        @foreach (['current_password' => 'Current password', 'password' => 'New password (min 10 characters)', 'password_confirmation' => 'Confirm new password'] as $n => $l)
            <div class="mb-3"><label class="form-label fw-semibold">{{ $l }}</label><input type="password"
                    name="{{ $n }}" class="form-control"
                    autocomplete="{{ $n == 'current_password' ? 'current-password' : 'new-password' }}">
                @error($n)
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        @endforeach
        <div><button class="btn btn-primary">Change password</button></div>
    </form>
@endsection
