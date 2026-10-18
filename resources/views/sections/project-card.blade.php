@use('App\Support\Ui')
<div class="col-md-6 col-lg-4" data-cat="{{ $pr->category }}">
    <div class="card proj h-100 rv" style="--d:{{ ($i % 3) * 0.08 }}s">
        @if ($pr->image)
            <a class="proj-img" href="{{ url('/projects/' . $pr->id) }}">{!! Ui::pic($pr->image, $pr->title . ' screenshot', '(min-width:992px) 360px, (min-width:768px) 50vw, 100vw') !!}<span><i
                    class="bi bi-arrow-up-right"></i></span></a>@else<a class="proj-img ph"
                href="{{ url('/projects/' . $pr->id) }}">Screenshot</a>
        @endif
        <div class="p-4 d-flex flex-column flex-grow-1"><span
                class="small text-primary fw-semibold">{{ $pr->category }}</span>
            @if ($pr->challenge)
                <span class="badge text-bg-warning ms-1">Case study</span>
            @endif
            <h3 class="h5 mt-1"><a class="text-reset text-decoration-none"
                    href="{{ url('/projects/' . $pr->id) }}">{{ $pr->title }}</a></h3>
            <p class="text-body-secondary small">{{ Ui::plain($pr->description, 110) }}</p>
            <div class="mb-2">
                @foreach (Ui::list($pr->tech) as $t)
                    <span class="chip">{{ $t }}</span>
                @endforeach
            </div>
            <div class="mt-auto pt-2 d-flex gap-2"><a class="btn btn-primary btn-sm"
                    href="{{ url('/projects/' . $pr->id) }}">View details</a>
                @if ($pr->demo)
                    <a class="btn btn-ghost btn-sm" href="{{ $pr->demo }}" target="_blank"
                        rel="noopener noreferrer">Live Demo</a>
                @endif
            </div>
        </div>
    </div>
</div>
