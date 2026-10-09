@use('App\Support\Ui')
<div class="col-md-6 col-lg-4"><a class="card svc h-100 p-4 rv text-decoration-none" style="--d:{{ ($i % 3) * 0.08 }}s"
        href="{{ url('/services/' . $sv->slug) }}">
        <div class="sic"><i class="bi {{ Ui::icon($sv->icon, $sv->title) }}"></i></div>
        <h3 class="h5 text-reset">{{ $sv->title }}</h3>
        <p class="text-body-secondary">{{ $sv->description }}</p>
        <span class="fw-semibold mt-auto text-primary">Learn more <i class="bi bi-arrow-right"></i></span>
    </a></div>
