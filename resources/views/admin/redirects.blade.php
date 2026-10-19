@extends('admin.base')
@section('c')
    <div class="mb-4">
        <h1 class="h4 mb-0">Redirects &amp; 404</h1>
        <p class="text-body-secondary small mb-0">Send old addresses to the right page, and find links that are broken</p>
    </div>
    <ul class="nav nav-pills mb-3 gap-1">
        @foreach (['redirects' => 'Redirects', 'errors' => 'Broken links (404)', 'bots' => 'Bots & noise', 'ignored' => 'Ignored'] as $k => $l)
            <li class="nav-item"><a class="nav-link {{ $tab == $k ? 'active' : '' }}"
                    href="/admin/redirects?tab={{ $k }}">{{ $l }} <span
                        class="badge text-bg-{{ $k == 'errors' && $counts[$k] ? 'warning' : 'secondary' }}">{{ $counts[$k] }}</span></a>
            </li>
        @endforeach
    </ul>

    @if ($tab == 'redirects')
        <form method="post" action="/admin/redirects" class="acard p-4 mb-3">@csrf
            <h2 class="h6 mb-3">Add a redirect</h2>
            <div class="row g-2 align-items-end">
                <div class="col-lg-4"><label class="form-label small mb-1">Old address</label><input name="from"
                        class="form-control" placeholder="/old-page" value="{{ old('from') }}" required></div>
                <div class="col-lg-4"><label class="form-label small mb-1">Send visitors to</label><input name="to"
                        class="form-control" placeholder="/new-page" value="{{ old('to') }}" required></div>
                <div class="col-lg-2"><label class="form-label small mb-1">Type</label><select name="code"
                        class="form-select">
                        <option value="301">301 permanent</option>
                        <option value="302">302 temporary</option>
                    </select></div>
                <div class="col-lg-2"><button class="btn btn-primary w-100">Save</button></div>
            </div>
            @error('from')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
            <div class="form-text mt-2">Use the part after your domain, for example <code>/blog/old-title</code>. 301 tells
                Google the page moved for good. If the old address already has a redirect, saving replaces it. A redirect
                only works for addresses where no page exists.</div>
        </form>
        <form method="get" class="mb-3"><input type="hidden" name="tab" value="redirects"><input name="q"
                class="form-control" style="max-width:320px" placeholder="Search redirects" value="{{ request('q') }}">
        </form>
        <div class="acard">
            @forelse($items as $x)
                <div class="row-item">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate">{{ $x->from_path }} <i
                                class="bi bi-arrow-right mx-1 text-body-secondary"></i> {{ $x->to_url }}</div>
                        <div class="small text-body-secondary">{{ $x->hits }} visit(s)@if ($x->last_hit_at)
                                · last {{ \Carbon\Carbon::parse($x->last_hit_at)->diffForHumans() }}
                            @endif
                        </div>
                    </div>
                    <span class="badge text-bg-light border">{{ $x->code }}</span>
                    @if ($x->auto)
                        <span class="badge text-bg-info" title="Created automatically when a slug was changed">auto</span>
                    @endif
                    <form method="post" action="/admin/redirects/{{ $x->id }}"
                        onsubmit="return confirm('Delete this redirect?')">@csrf @method('DELETE')<button
                            class="icon-btn del" title="Delete"><i class="bi bi-trash"></i></button></form>
                </div>
            @empty
                <div class="row-item text-body-secondary py-5 justify-content-center">No redirects yet.</div>
            @endforelse
        </div>
    @else
        @if ($tab == 'bots' && $counts['bots'])
            <form method="post" action="/admin/redirects/404/clear-bots" class="mb-3"
                onsubmit="return confirm('Clear all bot and noise entries?')">@csrf<button
                    class="btn btn-outline-danger btn-sm">Clear all</button></form>
        @endif
        <p class="small text-body-secondary">
            @if ($tab == 'errors')
                Addresses that visitors or Google tried to open but that do not exist. Most visited first. Type the right
                address and press Redirect.
            @elseif($tab == 'bots')
                Requests from scanners and browsers, such as wp-login.php, .env or favicon.ico. You can ignore these.
            @else
                Addresses you chose to ignore.
            @endif
        </p>
        <div class="acard">
            @forelse($items as $x)
                <div class="row-item flex-wrap">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate"><a class="text-decoration-none text-body"
                                href="{{ url($x->path) }}" target="_blank" rel="noopener">{{ $x->path }}</a></div>
                        <div class="small text-body-secondary">{{ $x->hits }} time(s) · last
                            {{ \Carbon\Carbon::parse($x->last_seen)->diffForHumans() }}@if ($x->referrer)
                                · from {{ $x->referrer }}
                            @endif
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        @if ($tab == 'errors')
                            <form method="post" action="/admin/redirects/404/{{ $x->id }}/fix"
                                class="d-flex gap-1">@csrf<input name="to" class="form-control form-control-sm"
                                    style="width:200px" placeholder="/right-page" required><button
                                    class="btn btn-sm btn-primary">Redirect</button></form>
                            <form method="post" action="/admin/redirects/404/{{ $x->id }}/ignore">@csrf<button
                                    class="btn btn-sm btn-outline-secondary">Ignore</button></form>
                        @elseif($tab == 'ignored')
                            <form method="post" action="/admin/redirects/404/{{ $x->id }}/restore">@csrf<button
                                    class="btn btn-sm btn-outline-secondary">Restore</button></form>
                        @endif
                        <form method="post" action="/admin/redirects/404/{{ $x->id }}"
                            onsubmit="return confirm('Remove from the list?')">@csrf @method('DELETE')<button
                                class="icon-btn del" title="Remove"><i class="bi bi-trash"></i></button></form>
                    </div>
                </div>
            @empty
                <div class="row-item text-body-secondary py-5 justify-content-center">
                    {{ $tab == 'errors' ? 'No broken links found. 🎉' : 'Nothing here.' }}</div>
            @endforelse
        </div>
    @endif
    <div class="mt-3 d-flex justify-content-center">{{ $items->links() }}</div>
@endsection
