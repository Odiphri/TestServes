<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Denied - {{ config('app.name', 'TestServes') }}</title>
    @include('partials.app-icons')
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <style>
        :root {
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
                radial-gradient(circle at top right, rgba(245, 158, 11, .18), transparent 34%),
                linear-gradient(135deg, #f8fafc 0%, #eef6f4 100%);
            color: var(--ink);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .denied-page {
            width: min(760px, 100%);
            padding: clamp(28px, 6vw, 56px);
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(255, 255, 255, .88);
            box-shadow: 0 24px 70px rgba(15, 23, 42, .12);
        }

        .badge {
            display: inline-flex;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--soft);
            color: var(--primary-dark);
            font-size: 13px;
            font-weight: 800;
        }

        h1 {
            margin: 22px 0 12px;
            font-size: clamp(34px, 7vw, 68px);
            line-height: 1;
            letter-spacing: 0;
        }

        .joke {
            max-width: 640px;
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
            'This page checked your rank and said, "Oga, your name is not on the list."',
            'Access denied. This URL is doing VIP table and your wristband is ordinary.',
            'This page saw your role and locked the door like exam malpractice was nearby.',
            'You tried to enter another rank\'s office. The page has reported you to the principal.',
            'This page said your permission scored F9 in authorization.',
            'You reached the gate, but the security man said your ID card is for another block.',
            'This page is not angry. It just knows you are not supposed to be here.',
            'Wrong rank, correct confidence. The page respects the audacity.',
            'This page checked your badge and said, "Nice try, but no."',
            'You knocked on the HOD door with student slippers. The system noticed.',
            'This route is for another rank. Your permission is still in nursery two.',
            'The page asked for clearance letter and your browser started sweating.',
            'This section said your access card is doing photocopy, not original.',
            'You came with confidence, but the permission list came with evidence.',
            'This page is not your class. Please stop peeping through the window.',
            'The system said your rank did not pass continuous assessment.',
            'This URL looked at your role and closed its notebook immediately.',
            'Access denied. Your permission is still waiting for promotion.',
            'This page said, "I know your type. Go back to your dashboard."',
            'You tried to enter staff meeting with student ID. Bold, but blocked.',
            'This rank mismatch is loud enough to disturb assembly.',
            'The page said your authorization result is still pending.',
            'You are logged in, yes. Invited here, no.',
            'This page locked itself faster than a teacher seeing noise makers.',
            'The URL said your role is not in the lesson note.',
            'Permission failed. Please revise chapter one: stay in your lane.',
            'This page is not rude. It is just very serious about rank.',
            'The access gate saw your role and started marking absent.',
            'You entered the wrong office with full chest. The system escorted you out.',
            'This page said your permission needs extra lesson.',
            'Access denied. This route is doing prefect duty at the gate.',
            'The page checked the register and your name was on another page.',
            'This role cannot enter here. Even the back button is advising you.',
        ];

        $joke = $jokes[array_rand($jokes)];
    @endphp

    <main class="denied-page">
        <span class="badge">403 - Access Denied</span>
        <h1>Not your rank.</h1>
        <p class="joke">{{ $joke }}</p>
        <div class="actions">
            <a class="btn btn-main" href="{{ url('/home') }}">Go to my dashboard</a>
            <a class="btn" href="{{ route('platform.home') }}">Go home</a>
            <a class="btn" href="#" onclick="history.length > 1 ? history.back() : window.location.assign('{{ route('platform.home') }}'); return false;">Go back</a>
        </div>
        <div class="tiny">Error 403. Permission did not pass promotion exam.</div>
    </main>
</body>
</html>
