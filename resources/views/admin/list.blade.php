@extends('admin.base')
@section('c')
    @php
        $canSort = array_key_exists('sort', $R['f']);
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0">{{ $R['label'] }}</h1>
            <p class="text-body-secondary small mb-0">{{ $items->count() }} item(s)@if ($canSort && $items->count() > 1)
                    · drag <i class="bi bi-grip-vertical"></i> to change the order <span id="sortnote" class="small"></span>
                @endif
            </p>
        </div>
        <a class="btn btn-primary" href="/admin/{{ $t }}/create"><i class="bi bi-plus-lg me-1"></i>Add new</a>
    </div>
    <div class="acard" @if ($canSort) data-sortable="/admin/{{ $t }}/reorder" @endif>
        @forelse($items as $i)
            <div class="row-item" data-id="{{ $i->id }}">
                @if ($canSort)
                    <span class="drag" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
                @endif
                @if (!empty($i->image))
                <img class="thumb" src="{{ asset($i->image) }}" alt="">@else<span class="thumb"><i
                            class="bi bi-file-earmark-text"></i></span>
                @endif
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate">{{ $i->{$R['title']} }}
                        @if (isset($i->published))
                            @if ($i->published)
                            <span class="badge text-bg-success">live</span>@else<span
                                    class="badge text-bg-secondary">draft</span>
                            @endif
                        @endif
                        @if (isset($i->visible) && !$i->visible)
                            <span class="badge text-bg-warning">hidden</span>
                        @endif
                    </div>
                    @if (!empty($R['sub']))
                        <div class="small text-body-secondary text-truncate">{{ $i->{$R['sub']} }}</div>
                    @endif
                </div>
                <div class="d-flex gap-2 flex-shrink-0"><a class="icon-btn"
                        href="/admin/{{ $t }}/{{ $i->id }}/edit" title="Edit"><i
                            class="bi bi-pencil"></i></a>
                    <form method="post" action="/admin/{{ $t }}/{{ $i->id }}"
                        onsubmit="return confirm('Delete this item?')">@csrf @method('DELETE')<button class="icon-btn del"
                            title="Delete"><i class="bi bi-trash"></i></button></form>
                </div>
            </div>
        @empty<div class="row-item text-body-secondary py-5 justify-content-center">Nothing here yet. Click "Add new" to
                create the first one.</div>
        @endforelse
    </div>
@endsection
