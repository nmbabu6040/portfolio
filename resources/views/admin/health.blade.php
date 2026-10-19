@extends('admin.base')
@section('c')
    @php
        $icon = [
            'ok' => ['bi-check-circle-fill', 'success'],
            'warn' => ['bi-exclamation-triangle-fill', 'warning'],
            'bad' => ['bi-x-octagon-fill', 'danger'],
            'info' => ['bi-info-circle-fill', 'secondary'],
        ];
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0">Site health</h1>
            <p class="text-body-secondary small mb-0">A quick check of your server, settings and backups</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge text-bg-{{ $bad ? 'danger' : 'success' }} fs-6">{{ $bad }} problem(s)</span>
            <span class="badge text-bg-{{ $warn ? 'warning' : 'success' }} fs-6">{{ $warn }} warning(s)</span>
            <a class="btn btn-outline-secondary" href="/admin/health"><i class="bi bi-arrow-repeat me-1"></i>Check again</a>
        </div>
    </div>
    @foreach ($sections as $title => $rows)
        <div class="acard mb-3">
            <div class="card-header border-0 pt-3 px-4 pb-0">
                <h2 class="h6 mb-0">{{ $title }}</h2>
            </div>
            <div class="mt-2">
                @foreach ($rows as $x)
                    <div class="row-item align-items-start">
                        <i class="bi {{ $icon[$x['st']][0] }} text-{{ $icon[$x['st']][1] }} mt-1"></i>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold">{{ $x['label'] }}</div>
                            @if ($x['detail'])
                                <div class="small text-body-secondary">{{ $x['detail'] }}</div>
                            @endif
                            @if ($x['fix'])
                                <div class="small text-primary">How to fix: {{ $x['fix'] }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
    <div class="acard mb-3">
        <div class="card-header border-0 pt-3 px-4 pb-0 d-flex justify-content-between">
            <h2 class="h6 mb-0">Recent errors</h2><span
                class="badge text-bg-{{ $errCount ? 'danger' : 'success' }}">{{ $errCount }} in the last 24
                hours</span>
        </div>
        <div class="mt-2">
            @forelse($errList as $e)
                <div class="row-item align-items-start"><i class="bi bi-bug text-danger mt-1"></i>
                    <div class="min-w-0">
                        <div class="small text-body-secondary">{{ $e['time'] }}</div>
                        <div class="small" style="overflow-wrap:anywhere">{{ $e['msg'] }}</div>
                    </div>
                </div>
            @empty
                <div class="row-item text-body-secondary"><i class="bi bi-check-circle-fill text-success"></i>No errors
                    found in the log.</div>
            @endforelse
        </div>
        <p class="small text-body-secondary px-4 pb-3 mb-0">Full details are in the file
            <code>storage/logs/laravel.log</code>.</p>
    </div>
@endsection
