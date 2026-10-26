@php $fv = \App\Support\Ui::favicon(\App\Models\Setting::map()); @endphp
@if ($fv['icon'])
    <link rel="icon" @if ($fv['type']) type="{{ $fv['type'] }}" @endif href="{{ $fv['icon'] }}">
@endif
@if ($fv['apple'])
    <link rel="apple-touch-icon" href="{{ $fv['apple'] }}">
@endif
