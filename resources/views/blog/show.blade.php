@extends('layouts.app')
@php
    $nm = $s['name'] ?? config('portfolio.name');
    $link = url()->current();
    $desc = \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?: $post->body), 150);
    $og = null;
    if ($post->image) {
        $jpg = preg_replace('/\.webp$/', '.jpg', $post->image);
        $og = file_exists(public_path($jpg)) ? $jpg : $post->image;
    }
    $mins = max(1, (int) ceil(str_word_count(strip_tags($post->body)) / 200));
    $cerr = $errors->getBag('comment');
@endphp
@section('title', $post->seo_title ?: $post->title . ' | ' . $nm)
@section('desc', $post->seo_description ?: $desc)
@section('og_type', 'article')
@if ($og)
    @section('og_image', asset($og))
@endif
@push('head')
    <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => $post->title, 'datePublished' => $post->created_at->toIso8601String(), 'dateModified' => $post->updated_at->toIso8601String(), 'image' => $og ? asset($og) : null, 'articleSection' => $cat->title ?? null, 'commentCount' => $commentCount, 'author' => ['@type' => 'Person', 'name' => $nm], 'mainEntityOfPage' => $link], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush
@section('content')
    <article class="py-5">
        <div class="container" style="max-width:780px">
            @if ($cat)
                <a class="badge text-bg-primary text-decoration-none mb-2"
                    href="{{ url('/blog/category/' . $cat->slug) }}">{{ $cat->title }}</a>
            @endif
            <p class="lbl">{{ $post->created_at->format('d M Y') }} · {{ $mins }} min read</p>
            <h1>{{ $post->title }}</h1>
            @if ($post->has_affiliate)
                <div class="aff-note"><i class="bi bi-info-circle"></i><span>Disclosure: this post contains affiliate links.
                        If you buy or sign up through them, I may earn a commission at no extra cost to you. <a
                            href="{{ url('/affiliate-disclosure') }}">Learn more</a></span></div>
            @endif
            @if ($post->image)
                {!! \App\Support\Ui::pic(
                    $post->image,
                    $post->title,
                    '(min-width:992px) 780px, 100vw',
                    'img-fluid rounded-4 mb-4 w-100',
                    'high',
                ) !!}
            @endif
            <div class="post-body">{!! \App\Support\Html::render($post->body) !!}</div>
            <hr class="my-4">

            <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                <button type="button" class="like-btn {{ $liked ? 'on' : '' }}"
                    data-like="{{ url('/blog/' . $post->slug . '/like') }}" data-token="{{ csrf_token() }}"
                    aria-pressed="{{ $liked ? 'true' : 'false' }}"><i
                        class="bi {{ $liked ? 'bi-heart-fill' : 'bi-heart' }}"></i><span
                        class="n">{{ $likes }}</span><span>Like</span></button>
                <a class="like-btn" href="#comments"><i
                        class="bi bi-chat-dots"></i><span>{{ $commentCount }}</span><span>{{ $commentCount == 1 ? 'comment' : 'comments' }}</span></a>
                <span class="text-body-secondary small" title="How many times the share buttons were used on this site"><i
                        class="bi bi-share me-1"></i><span data-shares>{{ (int) $post->share_count }}</span> shares</span>
            </div>
            @include('sections.share', [
                'shareUrl' => $link,
                'shareTitle' => $post->title,
                'track' => url('/blog/' . $post->slug . '/share'),
            ])

            <section id="comments" class="mt-5">
                <h2 class="h4 mb-3">Comments ({{ $commentCount }})</h2>
                @if (session('comment_ok'))
                    <div class="alert alert-success" role="status">{{ session('comment_ok') }}</div>
                @endif
                @if (session('comment_err'))
                    <div class="alert alert-danger" role="alert">{{ session('comment_err') }}</div>
                @endif
                @foreach ($comments as $c)
                    <div class="cmt" id="c{{ $c->id }}">
                        <span class="av av-i">{{ strtoupper(mb_substr($c->name, 0, 1)) }}</span>
                        <div class="cmt-b">
                            <div><strong>{{ $c->name }}</strong>
                                @if ($c->is_admin)
                                    <span class="badge text-bg-primary">Author</span>
                                @endif <span
                                    class="small text-body-secondary">{{ $c->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="cmt-t">{!! nl2br(e($c->body)) !!}</div>
                            @if ($post->comments_open)
                                <button type="button" class="btn btn-link btn-sm p-0" data-reply="{{ $c->id }}"
                                    data-name="{{ $c->name }}">Reply</button>
                            @endif
                        </div>
                    </div>
                    @foreach ($replies[$c->id] ?? [] as $rp)
                        <div class="cmt cmt-r" id="c{{ $rp->id }}">
                            <span class="av av-i">{{ strtoupper(mb_substr($rp->name, 0, 1)) }}</span>
                            <div class="cmt-b">
                                <div><strong>{{ $rp->name }}</strong>
                                    @if ($rp->is_admin)
                                        <span class="badge text-bg-primary">Author</span>
                                    @endif <span
                                        class="small text-body-secondary">{{ $rp->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="cmt-t">{!! nl2br(e($rp->body)) !!}</div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
                @if (!$comments->count())
                    <p class="text-body-secondary">No comments yet. Be the first to comment.</p>
                @endif

                @if ($post->comments_open)
                    <form method="post" action="{{ url('/blog/' . $post->slug . '/comment') }}" id="cform"
                        class="card p-4 mt-4" novalidate>@csrf
                        <h3 class="h6">Leave a comment</h3>
                        <div id="creply" class="alert alert-info small py-2 d-none">Replying to <b></b> <button
                                type="button" class="btn btn-sm btn-link p-0 ms-2" data-cancel-reply>Cancel</button></div>
                        @if ($cerr->any())
                            <div class="alert alert-danger small py-2">
                                <ul class="mb-0">
                                    @foreach ($cerr->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <input type="hidden" name="parent_id" id="parent_id" value="">
                        <input type="hidden" name="_t"
                            value="{{ \Illuminate\Support\Facades\Crypt::encryptString((string) time()) }}">
                        <div class="hp" aria-hidden="true"><label>Leave this empty<input name="website" tabindex="-1"
                                    autocomplete="off"></label></div>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label" for="c-name">Name</label><input
                                    class="form-control" id="c-name" name="name" maxlength="60"
                                    value="{{ old('name') }}" required></div>
                            <div class="col-md-6"><label class="form-label" for="c-email">Email (never shown)</label><input
                                    class="form-control" id="c-email" type="email" name="email" maxlength="150"
                                    value="{{ old('email') }}" required></div>
                            <div class="col-12"><label class="form-label" for="c-body">Comment</label>
                                <textarea class="form-control" id="c-body" name="body" rows="4" maxlength="2000" required>{{ old('body') }}</textarea>
                            </div>
                            <div class="col-12 d-flex flex-wrap align-items-center gap-3"><button
                                    class="btn btn-primary">Post comment</button><span
                                    class="small text-body-secondary">Comments are shown after approval.</span></div>
                        </div>
                    </form>
                @else
                    <p class="text-body-secondary mt-3">Comments are closed for this post.</p>
                @endif
            </section>

            <a class="btn btn-ghost mt-4" href="{{ url('/blog') }}"><i class="bi bi-arrow-left me-1"></i>All posts</a>
        </div>
    </article>
    @if ($related->count())
        <section class="sec alt">
            <div class="container">
                <h2 class="h3 mb-4">Related posts</h2>
                <div class="row g-4">
                    @foreach ($related as $i => $po)
                        @include('sections.post-card')
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
