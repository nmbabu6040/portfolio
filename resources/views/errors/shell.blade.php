@php
    $s = \App\Models\Setting::map();
    $nm = $s['name'] ?? config('portfolio.name');
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') | {{ $nm }}</title>
    <link rel="icon" href="{{ !empty($s['favicon']) ? asset($s['favicon']) : asset('favicon.ico') }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Plus+Jakarta+Sans:wght@800&display=swap"
        rel="stylesheet">
    <style>
        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: radial-gradient(800px 400px at 80% -10%, rgba(124, 58, 237, .35), transparent), #0b1020;
            color: #e5e9f7;
            font-family: Inter, system-ui, sans-serif;
            text-align: center
        }

        .box {
            max-width: 560px;
            width: 100%
        }

        .brand {
            display: inline-block;
            margin-bottom: 28px;
            color: #a5b4fc;
            text-decoration: none;
            font-weight: 600;
            letter-spacing: .02em
        }

        .code {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-weight: 800;
            font-size: clamp(5rem, 22vw, 9rem);
            line-height: 1;
            background: linear-gradient(135deg, #6366f1, #a855f7 55%, #22d3ee);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent
        }

        h1 {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: clamp(1.6rem, 5vw, 2.2rem);
            margin: 8px 0 12px;
            color: #fff
        }

        p {
            color: #a9b4d4;
            font-size: 1.05rem;
            line-height: 1.7;
            margin: 0 0 28px
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center
        }

        .b {
            display: inline-block;
            padding: 12px 22px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, .22);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            background: rgba(255, 255, 255, .06);
            transition: .2s
        }

        .b:hover {
            background: rgba(255, 255, 255, .14)
        }

        .b.p {
            border: 0;
            background: linear-gradient(135deg, #4f46e5, #7c3aed 55%, #06b6d4)
        }
    </style>
</head>

<body>
    <main class="box">
        <a class="brand" href="{{ url('/') }}">{{ $nm }}</a>
        <div class="code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <div class="actions">
            @hasSection('actions')
                @yield('actions')@else<a class="b p" href="{{ url('/') }}">Back to home</a><a class="b"
                    href="{{ url('/contact') }}">Contact me</a>
            @endif
        </div>
    </main>
</body>

</html>
