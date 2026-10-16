@php
    $here = hash('sha256', (string) request()->cookie('dev_id', ''));
@endphp
<div class="acard p-4 mt-4" style="max-width:760px">
    <h2 class="h6"><i class="bi bi-bell me-2 text-primary"></i>Login alerts</h2>
    <p class="text-body-secondary small">Get an email when your account is used to log in from a new device or browser.
        The email goes to <b>{{ auth()->user()->email }}</b>.</p>
    @if (!$smtp)
        <div class="alert alert-warning small">Email is not set up yet, so alerts cannot be sent. An Admin must add the
            SMTP settings in Profile &amp; Settings.</div>
    @endif
    <form method="post" action="/admin/security/alerts" class="d-flex align-items-center gap-3 mb-4">@csrf
        <div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" name="login_alerts"
                value="1" id="la" @checked($alerts)><label class="form-check-label"
                for="la">Send me login alerts</label></div>
        <button class="btn btn-sm btn-primary">Save</button>
    </form>
    <h3 class="h6">Your devices</h3>
    @forelse($devices as $d)
        <div class="d-flex justify-content-between align-items-center border-top py-2 small">
            <div><b>{{ \App\Support\LoginAlert::describe($d->user_agent) }}</b>
                @if ($d->token_hash === $here)
                    <span class="badge text-bg-success">this device</span>
                @endif
                <div class="text-body-secondary">{{ $d->ip }}</div>
            </div>
            <span
                class="text-body-secondary text-nowrap">{{ \Carbon\Carbon::parse($d->last_seen_at)->format('d M Y, H:i') }}</span>
        </div>
    @empty
        <p class="small text-body-secondary mb-0">No devices recorded yet. Your device is saved the next time you log
            in.</p>
    @endforelse
</div>
