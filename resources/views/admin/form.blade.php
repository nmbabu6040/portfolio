@extends('admin.base')
@section('c')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h4 mb-0">{{ $item ? 'Edit' : 'Add' }} · {{ $R['label'] }}</h1>
        <a class="btn btn-outline-secondary" href="/admin/{{ $t }}"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <form method="post" enctype="multipart/form-data" class="acard p-4" style="max-width:860px"
        action="/admin/{{ $t }}{{ $item ? '/' . $item->id : '' }}">@csrf
        @include('admin.fields', ['f' => $R['f'], 'v' => $item])
        <div class="d-flex gap-2 pt-2"><button class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save</button><a
                class="btn btn-outline-secondary" href="/admin/{{ $t }}">Cancel</a></div>
    </form>
@endsection
