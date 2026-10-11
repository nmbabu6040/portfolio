@use('App\Support\Ui')
<div class="col-md-6 col-lg-4">
    <article class="card proj h-100 rv" style="--d:{{ ($i % 3) * 0.08 }}s">
        @if ($po->image)
            <a class="proj-img" href="{{ url('/blog/' . $po->slug) }}" aria-label="{{ $po->title }}"><img
                    src="{{ asset($po->image) }}" loading="lazy" alt="{{ $po->title }}"><span><i
                        class="bi bi-arrow-up-right"></i></span></a>
        @else
            <a class="proj-img ph" href="{{ url('/blog/' . $po->slug) }}" aria-label="{{ $po->title }}"><i
                    class="bi bi-journal-text fs-1"></i></a>
        @endif
        <div class="p-4 d-flex flex-column flex-grow-1">
            @if (Ui::catTitle($po->category))
                <span class="small text-primary fw-semibold">{{ Ui::catTitle($po->category) }}</span>
            @endif
            <p class="small text-body-secondary mb-2">{{ $po->created_at->format('d M Y') }} ·
                {{ max(1, (int) ceil(str_word_count(strip_tags($po->body)) / 200)) }} min read</p>
            <h2 class="h5"><a class="text-reset text-decoration-none"
                    href="{{ url('/blog/' . $po->slug) }}">{{ $po->title }}</a></h2>
            <p class="text-body-secondary small">
                {{ \Illuminate\Support\Str::limit($po->excerpt ?: strip_tags($po->body), 120) }}</p>
            <a class="fw-semibold mt-auto" href="{{ url('/blog/' . $po->slug) }}">Read more <i
                    class="bi bi-arrow-right"></i></a>
        </div>
    </article>
</div>
