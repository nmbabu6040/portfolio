@extends('layouts.app')
@section('title', 'Blog | ' . ($s['name'] ?? config('portfolio.name')))
@section('desc', 'Articles and updates.')
@section('content')
<section class="py-5"><div class="container" style="max-width:800px"><p class="lbl">Blog</p><h1>Latest posts</h1>
@forelse($posts as $po)<article class="card p-4 mb-3"><h2 class="h4"><a class="text-decoration-none" href="{{ url('/blog/' . $po->slug) }}">{{ $po->title }}</a></h2><p class="text-body-secondary small">{{ $po->created_at->format('Y-m-d') }}</p><p class="mb-0">{{ $po->excerpt }}</p></article>@empty<p class="text-body-secondary">No posts yet.</p>@endforelse
{{ $posts->links() }}</div></section>
@endsection
