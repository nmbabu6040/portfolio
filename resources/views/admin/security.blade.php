@extends('admin.base')
@section('c')
    <div class="mb-4">
        <h1 class="h4 mb-0">Two-factor login (2FA)</h1>
        <p class="text-body-secondary small mb-0">Add a 6-digit code from your phone to the admin login</p>
    </div>

    @if ($newCodes)
        <div class="acard p-4 mb-4 border-warning">
            <h2 class="h6"><i class="bi bi-key me-2 text-warning"></i>Your recovery codes</h2>
            <p class="small text-body-secondary">Save these codes somewhere safe now (a password manager or on paper). Each
                code works only once and helps you log in if you lose your phone. They are shown only this one time.</p>
            <div class="rcodes">
                @foreach ($newCodes as $c)
                    <code>{{ $c }}</code>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-3"
                data-copyurl="{{ implode("\n", $newCodes) }}"><i class="bi bi-clipboard"></i> Copy codes</button>
        </div>
    @endif

    @if ($enabled)
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="acard p-4">
                    <h2 class="h6"><i class="bi bi-shield-check text-success me-2"></i>2FA is ON</h2>
                    <p class="text-body-secondary">You need a code from your authenticator app every time you log in.
                        Recovery codes left: <b>{{ $left }}</b></p>
                    <form method="post" action="/admin/security/recovery">@csrf
                        <label class="form-label">Create new recovery codes (enter your password)</label>
                        <div class="input-group"><input type="password" name="password" class="form-control" required
                                autocomplete="current-password"><button class="btn btn-outline-secondary">Create new
                                codes</button></div>
                        <div class="form-text">The old codes stop working.</div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="acard p-4">
                    <h2 class="h6"><i class="bi bi-shield-x text-danger me-2"></i>Turn off 2FA</h2>
                    <p class="text-body-secondary small">Your admin login will need only the email and password again.</p>
                    <form method="post" action="/admin/security/disable"
                        onsubmit="return confirm('Turn off two-factor login?')">@csrf
                        <label class="form-label">Enter your password to confirm</label>
                        <div class="input-group"><input type="password" name="password" class="form-control" required
                                autocomplete="current-password"><button class="btn btn-outline-danger">Turn off</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="acard p-4" style="max-width:760px">
            <h2 class="h6 mb-3">Set up in 3 steps</h2>
            <ol class="text-body-secondary ps-3">
                <li class="mb-3"><b class="text-body">Install an authenticator app</b> on your phone, for example Google
                    Authenticator, Microsoft Authenticator or Authy.</li>
                <li class="mb-3"><b class="text-body">Add this account</b> in the app: scan the QR code, or choose "enter
                    a setup key" and type the key below.
                    <div class="d-flex flex-wrap gap-4 align-items-center mt-3">
                        <div class="qrbox">
                            <div id="qr" data-uri="{{ $setup['uri'] }}" style="width:170px;height:170px"></div>
                        </div>
                        <div>
                            <div class="small text-body-secondary">Setup key (if you cannot scan)</div><code
                                class="fs-6 user-select-all">{{ implode(' ', str_split($setup['secret'], 4)) }}</code>
                            <div class="small text-body-secondary mt-1">Type: time based, 6 digits</div>
                        </div>
                    </div>
                </li>
                <li><b class="text-body">Enter the 6-digit code</b> shown in the app to turn it on.</li>
            </ol>
            <form method="post" action="/admin/security/enable">@csrf
                <div class="input-group" style="max-width:340px"><input name="code"
                        class="form-control form-control-lg text-center" inputmode="numeric" autocomplete="one-time-code"
                        maxlength="8" placeholder="123456" required><button class="btn btn-primary">Turn on</button></div>
                @error('code')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </form>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.js"></script>
        <script>
            (function() {
                var q = document.getElementById('qr');
                if (!q || typeof qrcode === 'undefined') {
                    if (q) q.parentNode.style.display = 'none';
                    return;
                }
                var qr = qrcode(0, 'M');
                qr.addData(q.dataset.uri);
                qr.make();
                q.innerHTML = qr.createSvgTag(5, 0);
                var s = q.querySelector('svg');
                if (s) {
                    s.style.width = '170px';
                    s.style.height = '170px';
                }
            })();
        </script>
    @endif
@endsection
