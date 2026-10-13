@if (!empty($s['logo']))
    {!! \App\Support\Ui::pic($s['logo'], $nm . ' logo', '200px', 'logo-img', 'eager') !!}
@elseif(file_exists(public_path('logo.png')))
    {!! \App\Support\Ui::pic('logo.png', $nm . ' logo', '200px', 'logo-img', 'eager') !!}
@else
    <span class="logo-m">{{ $ini }}</span>
@endif
