@php
    $u = urlencode($shareUrl);
    $tt = urlencode($shareTitle);
    $links = [
        ['Facebook', 'bi-facebook', "https://www.facebook.com/sharer/sharer.php?u=$u"],
        ['X', 'bi-twitter-x', "https://twitter.com/intent/tweet?url=$u&text=$tt"],
        ['LinkedIn', 'bi-linkedin', "https://www.linkedin.com/sharing/share-offsite/?url=$u"],
        ['WhatsApp', 'bi-whatsapp', "https://wa.me/?text=$tt%20$u"],
        ['Telegram', 'bi-telegram', "https://t.me/share/url?url=$u&text=$tt"],
        ['Email', 'bi-envelope', "mailto:?subject=$tt&body=$u"],
    ];
@endphp
<div class="share">
    <span class="share-l">Share this post</span>
    <div class="d-flex flex-wrap gap-2">
        @foreach ($links as [$n, $ic, $href])
            <a class="share-b" href="{{ $href }}" target="_blank" rel="noopener noreferrer"
                aria-label="Share on {{ $n }}"><i
                    class="bi {{ $ic }}"></i><span>{{ $n }}</span></a>
        @endforeach
        <button type="button" class="share-b" data-copy="{{ $shareUrl }}"><i class="bi bi-link-45deg"></i><span>Copy
                link</span></button>
        <button type="button" class="share-b d-none" data-native data-url="{{ $shareUrl }}"
            data-title="{{ $shareTitle }}"><i class="bi bi-box-arrow-up"></i><span>Share…</span></button>
    </div>
</div>
