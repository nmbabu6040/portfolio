@if (isset($reviews) && $reviews->count())
    <section class="sec alt">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <p class="eyebrow">Testimonials</p>
                    <h2 class="sec-title mb-0">What clients say</h2>
                </div>
                <div class="sl-nav"><button type="button" class="sl-btn" data-sl-prev aria-label="Previous review"><i
                            class="bi bi-arrow-left"></i></button><button type="button" class="sl-btn" data-sl-next
                        aria-label="Next review"><i class="bi bi-arrow-right"></i></button></div>
            </div>
            <div class="slider" data-slider role="region" aria-roledescription="carousel"
                aria-label="Client testimonials">
                <div class="sl-track" tabindex="0">
                    @foreach ($reviews as $i => $r)
                        <figure class="sl-item card" role="group" aria-roledescription="slide"
                            aria-label="{{ $i + 1 }} of {{ $reviews->count() }}">
                            <i class="bi bi-quote fs-1 text-primary lh-1"></i>
                            <blockquote class="mb-3">{{ $r->quote }}</blockquote>
                            <figcaption class="mt-auto d-flex align-items-center gap-3">
                                @if ($r->photo)
                                    {!! \App\Support\Ui::pic($r->photo, '', '48px', 'av') !!}@else<span
                                        class="av av-i">{{ strtoupper(mb_substr($r->name, 0, 1)) }}</span>
                                @endif
                                <div><strong>{{ $r->name }}</strong>
                                    @if ($r->role)
                                        <div class="small text-body-secondary">{{ $r->role }}</div>
                                        @endif @if ($r->url)
                                            <a class="small" href="{{ $r->url }}" target="_blank"
                                                rel="noopener noreferrer">View original</a>
                                        @endif
                                </div>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
                <div class="sl-dots" aria-hidden="true"></div>
            </div>
        </div>
    </section>
@endif
