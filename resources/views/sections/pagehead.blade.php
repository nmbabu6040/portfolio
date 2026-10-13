<section class="phead">
    <div class="blob b1"></div>
    <div class="container position-relative">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                @isset($crumb)
                    <li class="breadcrumb-item"><a href="{{ url($crumb[1]) }}">{{ $crumb[0] }}</a></li>
                @endisset
                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>
        <h1 class="phead-t">{{ $title }}</h1>
        @if (!empty($sub))
            <p class="phead-s">{{ \Illuminate\Support\Str::limit($sub, 150) }}</p>
        @endif
    </div>
</section>
