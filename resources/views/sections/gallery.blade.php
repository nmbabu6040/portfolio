@php
    try {
        $gm = \Illuminate\Support\Facades\DB::table('project_media')
            ->where('project_id', $project->id)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
    } catch (\Throwable $e) {
        $gm = collect();
    }
@endphp
@if ($gm->count())
    @php
        $gdata = $gm
            ->map(
                fn($m) => $m->type == 'image'
                    ? ['t' => 'i', 'src' => asset($m->path), 'cap' => $m->caption]
                    : ['t' => 'v', 'src' => $m->url, 'cap' => $m->caption],
            )
            ->values();
    @endphp
    <div class="mt-4">
        <h2 class="h5 mb-3">Gallery</h2>
        <div class="gal-grid">
            @foreach ($gm as $i => $m)
                <button type="button" class="gal-t" data-gal="{{ $i }}"
                    aria-label="{{ $m->type == 'image' ? 'Open image ' . ($i + 1) : 'Play video' }}">
                    @if ($m->type == 'image')
                        {!! \App\Support\Ui::pic(
                            $m->path,
                            $m->caption ?: $project->title . ' screenshot ' . ($i + 1),
                            '(min-width:992px) 240px, 50vw',
                        ) !!}@else<span class="gal-v"><i class="bi bi-play-circle-fill"></i></span>
                    @endif
                </button>
            @endforeach
        </div>
        <script type="application/json" id="gal-data">{!! json_encode($gdata, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    </div>
    <div class="modal fade" id="gal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-0">
                <div class="modal-body p-2 position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        style="z-index:3" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div id="gal-stage" class="gal-stage"></div>
                    <p id="gal-cap" class="text-center small mt-2 mb-0"></p>
                    <button type="button" class="gal-nav gal-prev" aria-label="Previous"><i
                            class="bi bi-chevron-left"></i></button>
                    <button type="button" class="gal-nav gal-next" aria-label="Next"><i
                            class="bi bi-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </div>
@endif
