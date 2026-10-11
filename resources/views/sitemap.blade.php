{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach (['/', '/about', '/experience', '/technology', '/projects', '/services', '/blog', '/contact', '/privacy', '/quote'] as $u)
        <url>
            <loc>{{ url($u) }}</loc>
        </url>
    @endforeach
    @foreach ($services as $sv)
        <url>
            <loc>{{ url('/services/' . $sv->slug) }}</loc>
        </url>
    @endforeach
    @foreach ($projects as $pr)
        <url>
            <loc>{{ url('/projects/' . $pr->id) }}</loc>
        </url>
    @endforeach
    @foreach ($posts as $po)
        <url>
            <loc>{{ url('/blog/' . $po->slug) }}</loc>
            <lastmod>{{ $po->updated_at->format('Y-m-d') }}</lastmod>
        </url>
    @endforeach
</urlset>
