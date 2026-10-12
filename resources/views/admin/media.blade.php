@extends('admin.base')
@section('c')
    @php
        $unused = collect($items)->filter(fn($m) => !$m['used'])->count();
        $total = collect($items)->sum('size');
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0">Media library</h1>
            <p class="text-body-secondary small mb-0">{{ count($items) }} image(s) · {{ number_format($total / 1048576, 1) }}
                MB · {{ $unused }} unused</p>
        </div>
        @if ($unused)
            <form method="post" action="/admin/media/cleanup"
                onsubmit="return confirm('Delete all unused images that are older than 1 day? This cannot be undone.')">
                @csrf<button class="btn btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Delete unused</button></form>
        @endif
    </div>
    <form method="post" action="/admin/media" enctype="multipart/form-data" class="acard p-4 mb-4">@csrf
        <label class="form-label fw-semibold">Upload images</label>
        <div class="d-flex flex-wrap gap-2"><input type="file" name="images[]" class="form-control"
                style="max-width:420px" accept="image/*" multiple required><button class="btn btn-primary"><i
                    class="bi bi-upload me-1"></i>Upload</button></div>
        <div class="form-text">Up to 20 images at once, 8 MB each. Images are converted to WebP and resized to 1600 px wide
            automatically.</div>
    </form>
    <div class="mgrid">
        @forelse($items as $m)
            <div class="acard mcard">
                <a href="{{ $m['url'] }}" target="_blank" rel="noopener"><img class="mthumb" src="{{ $m['url'] }}"
                        loading="lazy" alt=""></a>
                <div class="p-3">
                    <div class="small fw-semibold text-truncate" title="{{ $m['name'] }}">{{ $m['name'] }}</div>
                    <div class="small text-body-secondary">{{ number_format($m['size'] / 1024) }} KB ·
                        {{ \Carbon\Carbon::createFromTimestamp($m['time'])->format('d M Y') }}</div>
                    <div class="my-2">
                        @if ($m['used'])
                            <span class="badge text-bg-success">Used</span> <span
                            class="small text-body-secondary">{{ implode(', ', $m['used']) }}</span>@else<span
                                class="badge text-bg-secondary">Unused</span>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill"
                            data-copyurl="{{ url($m['url']) }}"><i class="bi bi-link-45deg"></i> Copy URL</button>
                        <form method="post" action="/admin/media/{{ $m['name'] }}"
                            onsubmit="return confirm('{{ $m['used'] ? 'This image is in use. Deleting it will break it on your site. Delete anyway?' : 'Delete this image?' }}')">
                            @csrf @method('DELETE')<button class="icon-btn del" title="Delete"><i
                                    class="bi bi-trash"></i></button></form>
                    </div>
                </div>
            </div>
        @empty
            <div class="acard p-5 text-center text-body-secondary" style="grid-column:1/-1">No images yet. Upload your first
                image above.</div>
        @endforelse
    </div>
@endsection
