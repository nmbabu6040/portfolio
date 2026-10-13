@if (isset($reviews) && $reviews->count())
    <section class="sec alt">
        <div class="container">
            <p class="eyebrow">Testimonials</p>
            <h2 class="sec-title">What clients say</h2>
            <div class="row g-4 mt-1">
                @foreach ($reviews as $i => $r)
                    <div class="col-md-6 col-lg-4">
                        <figure class="card h-100 p-4 rv mb-0" style="--d:{{ ($i % 3) * 0.08 }}s">
                            <i class="bi bi-quote fs-1 text-primary lh-1"></i>
                            <blockquote class="mb-3">{{ $r->quote }}</blockquote>
                            <figcaption class="mt-auto d-flex align-items-center gap-3">
                                @if ($r->photo)
                                    {!! \App\Support\Ui::pic($r->photo, '', '48px', 'av') !!}
                                @else
                                    <span class="av av-i">{{ strtoupper(mb_substr($r->name, 0, 1)) }}</span>
                                @endif
                                <div>
                                    <strong>{{ $r->name }}</strong>
                                    @if ($r->role)
                                        <div class="small text-body-secondary">{{ $r->role }}</div>
                                    @endif
                                    @if ($r->url)
                                        <a class="small" href="{{ $r->url }}" target="_blank"
                                            rel="noopener noreferrer">View original</a>
                                    @endif
                                </div>
                            </figcaption>
                        </figure>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
