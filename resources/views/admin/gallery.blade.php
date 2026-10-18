@php
    $gm = \Illuminate\Support\Facades\DB::table('project_media')
        ->where('project_id', $item->id)
        ->orderBy('sort')
        ->orderBy('id')
        ->get();
@endphp
<div class="acard p-4 mt-4" style="max-width:860px">
    <h2 class="h6 mb-1"><i class="bi bi-images me-2 text-primary"></i>Gallery ({{ $gm->count() }}/30)</h2>
    <p class="small text-body-secondary">More screenshots and videos for this project. They appear as a gallery on the
        project page. Drag <i class="bi bi-grip-horizontal"></i> to change the order.</p>
    <div id="gal-list" class="ggrid" data-order="/admin/projects/{{ $item->id }}/gallery/order">
        @forelse($gm as $m)
            <div class="gitem" data-id="{{ $m->id }}">
                <span class="drag gdrag" title="Drag to reorder"><i class="bi bi-grip-horizontal"></i></span>
                <form method="post" action="/admin/gallery/{{ $m->id }}" class="gdel-f"
                    onsubmit="return confirm('Remove this item from the gallery?')">@csrf @method('DELETE')<button
                        class="icon-btn del" title="Remove"><i class="bi bi-trash"></i></button></form>
                @if ($m->type == 'image')
                <img src="{{ asset($m->path) }}" alt="" loading="lazy">@else<div class="gvid">
                        <div><i class="bi bi-play-circle-fill"></i><small>Video</small></div>
                    </div>
                @endif
                <form method="post" action="/admin/gallery/{{ $m->id }}/caption">@csrf
                    <input name="caption" class="form-control form-control-sm" maxlength="150"
                        placeholder="Caption (optional)" value="{{ $m->caption }}">
                    <button class="btn btn-sm btn-light w-100 mt-1">Save caption</button>
                </form>
            </div>
        @empty
            <p class="small text-body-secondary mb-0">No gallery items yet.</p>
        @endforelse
    </div>
    <hr>
    <form method="post" action="/admin/projects/{{ $item->id }}/gallery" enctype="multipart/form-data">@csrf
        <div data-pickfield class="mb-3">
            <label class="form-label fw-semibold">Add images</label>
            <div class="d-flex flex-wrap gap-2">
                <input type="file" name="images[]" class="form-control" style="max-width:340px" accept="image/*"
                    multiple>
                <button type="button" class="btn btn-outline-secondary" data-pick><i
                        class="bi bi-images me-1"></i>Choose from library</button>
            </div>
            <img class="pick-prev d-none mt-2" alt="">
            <div class="small text-body-secondary pick-note mt-1"></div>
            <input type="hidden" name="library_pick" value="">
            <div class="form-text">Up to 20 images at once. They are converted to WebP automatically.</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Add a video link</label>
            <input name="video" class="form-control" style="max-width:520px"
                placeholder="https://www.youtube.com/watch?v=..." value="{{ old('video') }}">
            <div class="form-text">YouTube or Vimeo only. It loads only when a visitor clicks play.</div>
            @error('video')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <button class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add to gallery</button>
    </form>
</div>
