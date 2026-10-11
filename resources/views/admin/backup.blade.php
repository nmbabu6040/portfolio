@extends('admin.base')
@section('c')
    <div class="mb-4">
        <h1 class="h4 mb-0">Backup</h1>
        <p class="text-body-secondary small mb-0">Download a copy of your content and uploaded files</p>
    </div>
    <div class="row g-4">
        <div class="col-xl-6">
            <div class="acard p-4">
                <h2 class="h6 mb-3"><i class="bi bi-cloud-download me-2 text-primary"></i>Download backup</h2>
                <p class="text-body-secondary">The zip file contains:</p>
                <ul class="text-body-secondary">
                    <li><b>database.sql</b>: all your content, messages, settings and admin account</li>
                    <li><b>uploads/</b>: your photo, logo, project screenshots and blog images</li>
                </ul>
                <p class="small text-body-secondary">Last backup: <b>{{ $last ?? 'never' }}</b></p>
                <div class="alert alert-warning small">The file includes your admin password hash and encrypted SMTP
                    password. Keep it private.</div>
                <form method="post" action="/admin/backup/download">@csrf<button class="btn btn-primary"><i
                            class="bi bi-download me-1"></i>Download backup (.zip)</button></form>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="acard p-4">
                <h2 class="h6 mb-3"><i class="bi bi-arrow-counterclockwise me-2 text-primary"></i>How to restore</h2>
                <ol class="text-body-secondary mb-0">
                    <li>Set up the project on the new server or computer.</li>
                    <li>Run <code>php artisan migrate:fresh</code> (without <code>--seed</code>).</li>
                    <li>Import <code>database.sql</code> using phpMyAdmin (Import tab) or the MySQL command line.</li>
                    <li>Copy the <code>uploads</code> folder from the zip into <code>storage/app/public/</code>.</li>
                    <li>Run <code>php artisan storage:link</code>, then log in with your old admin account.</li>
                </ol>
            </div>
        </div>
    </div>
@endsection
