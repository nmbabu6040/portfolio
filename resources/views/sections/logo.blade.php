@if (!empty($s['logo']))
    <img class="logo-img" src="{{ asset($s['logo']) }}" alt="{{ $nm }} logo" height="40">
@elseif(file_exists(public_path('logo.png')))
    <img class="logo-img" src="{{ asset('logo.png') }}" alt="{{ $nm }} logo" height="40">
@else
    <span class="logo-m">{{ $ini }}</span>
@endif
