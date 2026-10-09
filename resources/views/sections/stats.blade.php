@if ($stats->count())
    <section class="band">
        <div class="container">
            <div class="row g-4 text-center">
                @foreach ($stats as $i => $st)
                    <div class="col-6 col-lg-3 rv" style="--d:{{ $i * 0.08 }}s">
                        <div class="band-n"
                            @if (preg_match('/^(\d{1,2})(\+?)$/', $st->value, $m)) data-to="{{ $m[1] }}" data-sx="{{ $m[2] }}" @endif>
                            {{ $st->value }}</div>
                        <div class="opacity-75">{{ $st->label }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
