<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Back soon | {{ $s['name'] ?? config('portfolio.name') }}</title>
    <link rel="icon" href="{{ !empty($s['favicon']) ? asset($s['favicon']) : asset('favicon.ico') }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Plus+Jakarta+Sans:wght@800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #0b1020;
            color: #e5e9f7;
            font-family: Inter, system-ui, sans-serif;
            text-align: center
        }

        .box {
            max-width: 520px
        }

        .ic {
            width: 84px;
            height: 84px;
            margin: 0 auto 24px;
            border-radius: 24px;
            display: grid;
            place-items: center;
            font-size: 2.4rem;
            color: #fff;
            background: linear-gradient(135deg, #4f46e5, #7c3aed 55%, #06b6d4)
        }

        h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: clamp(1.8rem, 5vw, 2.6rem);
            margin: 0 0 12px;
            color: #fff
        }

        p {
            color: #a9b4d4;
            font-size: 1.05rem;
            line-height: 1.7;
            margin: 0 0 16px
        }

        a {
            color: #a5b4fc
        }
    </style>
</head>

<body>
    <div class="box">
        @if (!empty($s['logo']))
            <img src="{{ asset($s['logo']) }}" alt="{{ $s['name'] ?? '' }}"
            style="height:56px;margin-bottom:24px;background:#fff;border-radius:12px;padding:6px">@else<div
                class="ic"><i class="bi bi-tools"></i></div>
        @endif
        <h1>We will be back soon</h1>
        <p>{{ $s['maintenance_message'] ?? 'This website is being updated. Please check back in a little while.' }}</p>
        @if (!empty($s['email']))
            <p>You can reach me at <a href="mailto:{{ $s['email'] }}">{{ $s['email'] }}</a></p>
        @endif
    </div>
</body>

</html>
