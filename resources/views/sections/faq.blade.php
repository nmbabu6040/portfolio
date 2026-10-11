@if (isset($faqs) && $faqs->count())
    @push('head')
        <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faqs->map(fn ($f) => ['@type' => 'Question', 'name' => $f->question, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer]])->values()->all()], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @endpush
    <div class="mt-5">
        <h2 class="h4 mb-3">Frequently asked questions</h2>
        <div class="accordion faq" id="faq">
            @foreach ($faqs as $i => $f)
                <div class="accordion-item">
                    <h3 class="accordion-header"><button class="accordion-button {{ $i ? 'collapsed' : '' }}"
                            type="button" data-bs-toggle="collapse" data-bs-target="#fq{{ $f->id }}"
                            aria-expanded="{{ $i ? 'false' : 'true' }}">{{ $f->question }}</button></h3>
                    <div id="fq{{ $f->id }}" class="accordion-collapse collapse {{ $i ? '' : 'show' }}"
                        data-bs-parent="#faq">
                        <div class="accordion-body">{!! nl2br(e($f->answer)) !!}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
