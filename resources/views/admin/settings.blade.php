@extends('admin.base')
@section('c')
    <h1 class="h3 mb-3">Profile &amp; Settings</h1>
    <form method="post" enctype="multipart/form-data" style="max-width:760px">@csrf
        @foreach ($g as $title => $f)
            <div class="card p-4 mb-4">
                <h2 class="h5 mb-3">{{ $title }}</h2>@include('admin.fields', ['f' => $f, 'v' => $s])
            </div>
        @endforeach
        <button class="btn btn-primary mb-4">Save all settings</button>
    </form>
    <form method="post" action="/admin/settings/test-mail" class="mb-5">@csrf<button class="btn btn-outline-secondary">Send
            test email to my contact address</button>
        <div class="form-text">Save the settings first, then test.</div>
    </form>
@endsection
