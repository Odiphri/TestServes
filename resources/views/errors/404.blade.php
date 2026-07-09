<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Missing - {{ config('app.name', 'TestServes') }}</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --ink: #102033;
            --muted: #64748b;
            --line: #dbe4ee;
            --primary: #0f766e;
            --primary-dark: #115e59;
            --soft: #ecfdf5;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 28px;
            background:
                radial-gradient(circle at top left, rgba(15, 118, 110, .13), transparent 34%),
                linear-gradient(135deg, #f8fafc 0%, #eef6f4 100%);
            color: var(--ink);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .missing-page {
            width: min(760px, 100%);
            padding: clamp(28px, 6vw, 56px);
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(255, 255, 255, .86);
            box-shadow: 0 24px 70px rgba(15, 23, 42, .12);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--soft);
            color: var(--primary-dark);
            font-size: 13px;
            font-weight: 800;
        }

        h1 {
            margin: 22px 0 12px;
            font-size: clamp(36px, 8vw, 76px);
            line-height: .96;
            letter-spacing: 0;
        }

        .joke {
            max-width: 620px;
            margin: 0 0 22px;
            color: var(--muted);
            font-size: clamp(17px, 3vw, 22px);
            line-height: 1.55;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            border-radius: 10px;
            border: 1px solid var(--line);
            color: var(--ink);
            font-weight: 800;
            text-decoration: none;
        }

        .btn-main {
            border-color: var(--primary);
            background: var(--primary);
            color: #fff;
        }

        .tiny {
            margin-top: 24px;
            color: #94a3b8;
            font-size: 13px;
        }
    </style>
</head>
<body>
    @php
        $jokes = [
            'The page ran off because it scored F9 in maths and could not calculate the URL.',
            'This page entered the wrong exam hall and submitted blank answers.',
            'The page was here before, but it saw the question paper and quietly left.',
            'This link tried to form a study group, but nobody showed up.',
            'The page failed attendance. Even the class rep cannot defend it.',
            'This URL is doing extra lessons somewhere else.',
            'The page has been promoted to another class, but nobody updated the timetable.',
            'The page said it was coming in five minutes. That was three terms ago.',
            'This link wrote its name on the attendance sheet and disappeared.',
            'The page is absent today. Reason: headache from too much homework.',
            'This URL entered the wrong class and nobody recognized it.',
            'The page tried to solve for X and lost itself in the process.',
            'This page forgot its portal ID at home.',
            'The page borrowed somebody\'s calculator and never returned.',
            'This link was last seen near the staff room pretending to be busy.',
            'The page submitted its assignment to the wrong teacher.',
            'This page is currently repeating the class.',
            'The URL got transferred, but nobody updated the register.',
            'This page went for break and did not come back.',
            'The page is not lost. It is just confidently unavailable.',
            'This link failed map reading and turned left at the wrong timetable.',
            'The page said it knows the answer, then opened a blank booklet.',
            'This URL is still looking for its seat number.',
            'The page got F9 in navigation and E8 in commitment.',
            'This page is hiding because it did not pay attention in class.',
            'The link has been sent to the principal for questioning.',
            'This page was supposed to be here, but it chose drama.',
            'The URL is doing revision somewhere nobody can find.',
            'This page took permission to use the restroom and vanished.',
            'The page mixed up its exam timetable and missed this route.',
        ];

        $joke = $jokes[array_rand($jokes)];
    @endphp

    <main class="missing-page">
        <span class="badge">404 - Missing Page</span>
        <h1>Page not found.</h1>
        <p class="joke">{{ $joke }}</p>
        <div class="actions">
            <a class="btn btn-main" href="{{ route('platform.home') }}">Go home</a>
            <a class="btn" href="{{ route('platform.login') }}">Login</a>
            <a class="btn" href="#" onclick="history.length > 1 ? history.back() : window.location.assign('{{ route('platform.home') }}'); return false;">Go back</a>
        </div>
        <div class="tiny">Error 404. The page did not pass continuous assessment.</div>
    </main>
</body>
</html>
