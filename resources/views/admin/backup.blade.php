@extends('admin.base')
@section('c')
    @php
        $last = $s['last_auto_backup'] ?? null;
        $failed = $last && str_contains($last, 'failed');
    @endphp
    <div class="mb-4">
        <h1 class="h4 mb-0">Backup</h1>
        <p class="text-body-secondary small mb-0">Keep a safe copy of your content and uploaded files</p>
    </div>
    <div class="row g-4">
        <div class="col-xl-6 d-flex flex-column gap-4">

            <div class="acard p-4">
                <h2 class="h6 mb-3"><i class="bi bi-arrow-repeat me-2 text-primary"></i>Automatic weekly backup</h2>
                <form method="post" action="/admin/backup/settings">@csrf
                    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox"
                            name="auto_backup" value="1" id="ab" @checked(!empty($s['auto_backup']))><label
                            class="form-check-label" for="ab">Create a backup every Sunday at 03:00</label></div>
                    <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox"
                            name="auto_backup_email" value="1" id="abe" @checked(!empty($s['auto_backup_email']))><label
                            class="form-check-label" for="abe">Email a database copy to
                            {{ $s['email'] ?? 'my contact email' }}</label></div>
                    @if (!$smtp)
                        <div class="alert alert-warning small">Email is not ready: set the SMTP settings and your contact
                            email in Profile &amp; Settings first. The backup on the server still works.</div>
                    @endif
                    <button class="btn btn-primary btn-sm">Save</button>
                </form>
                <hr>
                <p class="small mb-1"><b>Last automatic or manual run:</b></p>
                <p class="small {{ $failed ? 'text-danger' : 'text-body-secondary' }} mb-3">{{ $last ?? 'Never' }}</p>
                <form method="post" action="/admin/backup/run"
                    onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='Working, please wait...'">
                    @csrf<button class="btn btn-outline-primary"><i class="bi bi-play-circle me-1"></i>Run backup
                        now</button></form>
                <div class="form-text mt-2">Saves a full backup on the server, and emails the database copy if that switch
                    is on.</div>
            </div>

            <div class="acard p-4">
                <h2 class="h6 mb-3"><i class="bi bi-clock me-2 text-primary"></i>Make the weekly schedule run</h2>
                <p class="small text-body-secondary">The weekly backup only runs while Laravel's scheduler is running.</p>
                <p class="small mb-1"><b>On your live hosting (cPanel):</b> add a Cron Job that runs every minute:</p>
                <pre class="small bg-light p-2 rounded mb-3" style="white-space:pre-wrap">* * * * * /usr/local/bin/php /home/USERNAME/PATH-TO-SITE/artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</pre>
                <p class="small mb-1"><b>On your own computer (testing):</b> keep this running in a terminal in the project
                    folder:</p>
                <pre class="small bg-light p-2 rounded mb-2">php artisan schedule:work</pre>
                <p class="small text-body-secondary mb-0">Set <code>APP_TIMEZONE=Asia/Dhaka</code> in <code>.env</code> so
                    03:00 means Bangladesh time.</p>
            </div>
        </div>

        <div class="col-xl-6 d-flex flex-column gap-4">
            <div class="acard p-4">
                <h2 class="h6 mb-3"><i class="bi bi-hdd me-2 text-primary"></i>Backups saved on the server</h2>
                <p class="small text-body-secondary">The newest 4 are kept, older ones are deleted automatically. If the
                    server itself is lost these are lost too, so download one now and then.</p>
                @forelse($files as $f)
                    <div class="d-flex align-items-center gap-2 border-top py-2">
                        <div class="flex-grow-1 min-w-0">
                            <div class="small fw-semibold text-truncate">{{ $f['name'] }}</div>
                            <div class="small text-body-secondary">{{ \App\Support\Backup::human($f['size']) }} ·
                                {{ \Carbon\Carbon::createFromTimestamp($f['time'])->format('d M Y, H:i') }}</div>
                        </div>
                        <a class="icon-btn" href="/admin/backup/file/{{ $f['name'] }}" title="Download"><i
                                class="bi bi-download"></i></a>
                        <form method="post" action="/admin/backup/file/{{ $f['name'] }}"
                            onsubmit="return confirm('Delete this backup file?')">@csrf @method('DELETE')<button
                                class="icon-btn del" title="Delete"><i class="bi bi-trash"></i></button></form>
                    </div>
                @empty
                    <p class="small text-body-secondary mb-0">No backups on the server yet. Click "Run backup now".</p>
                @endforelse
            </div>

            <div class="acard p-4">
                <h2 class="h6 mb-3"><i class="bi bi-cloud-download me-2 text-primary"></i>Download a backup now</h2>
                <p class="small text-body-secondary">A zip with <b>database.sql</b> (everything, including user accounts)
                    and <b>uploads/</b> (your images). Last manual download: <b>{{ $s['last_backup'] ?? 'never' }}</b></p>
                <div class="alert alert-warning small">This file includes admin password hashes. Keep it private.</div>
                <form method="post" action="/admin/backup/download">@csrf<button class="btn btn-primary"><i
                            class="bi bi-download me-1"></i>Download backup (.zip)</button></form>
            </div>

            <div class="acard p-4">
                <h2 class="h6 mb-3"><i class="bi bi-arrow-counterclockwise me-2 text-primary"></i>How to restore</h2>
                <p class="small fw-semibold mb-1">From a full backup (server copy or download):</p>
                <ol class="small text-body-secondary">
                    <li>Set up the project, then run <code>php artisan migrate:fresh</code> (without <code>--seed</code>).
                    </li>
                    <li>Import <code>database.sql</code> in phpMyAdmin (Import tab).</li>
                    <li>Copy the <code>uploads</code> folder into <code>storage/app/public/</code>, run <code>php artisan
                            storage:link</code>, and log in with your old account.</li>
                </ol>
                <p class="small fw-semibold mb-1">From the emailed database copy (it has no user accounts):</p>
                <ol class="small text-body-secondary mb-0">
                    <li>Run <code>php artisan migrate:fresh</code> (without <code>--seed</code>).</li>
                    <li>Import its <code>database.sql</code>, then run <code>php artisan db:seed</code>. This creates the
                        admin account from <code>ADMIN_EMAIL</code> and <code>ADMIN_PASSWORD</code> in <code>.env</code>.
                    </li>
                    <li>Copy your <code>uploads</code> images back from a full backup.</li>
                </ol>
            </div>
        </div>
    </div>
@endsection
