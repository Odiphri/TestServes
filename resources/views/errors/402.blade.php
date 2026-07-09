@php
    $jokes = [
        'This portal is waiting for payment like a student waiting for break time.',
        'Access paused. The subscription scored absent in payment class.',
        'The school portal is locked until Finance marks payment as paid.',
        'Payment first, CBT later. Even exams respect receipts here.',
        'This portal tried to enter without paying school fees. We caught it at the gate.',
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Required - TestServes</title>
    <style>
        body { margin:0; min-height:100vh; display:grid; place-items:center; font-family:Inter,Arial,sans-serif; background:#f6f9fc; color:#102033; }
        .box { width:min(560px, calc(100% - 32px)); background:#fff; border:1px solid #dbe3ef; border-radius:8px; padding:28px; box-shadow:0 20px 60px rgba(16,32,51,.12); }
        h1 { margin:0 0 10px; font-size:32px; }
        p { color:#64748b; line-height:1.6; }
        a { display:inline-block; margin-top:12px; background:#0f766e; color:#fff; padding:10px 14px; border-radius:8px; text-decoration:none; font-weight:800; }
    </style>
</head>
<body>
    <main class="box">
        <h1>Payment Required</h1>
        <p>{{ $jokes[array_rand($jokes)] }}</p>
        <p>Please ask the school owner to submit payment from their TestServes owner dashboard, then wait for Finance Admin confirmation.</p>
        <a href="{{ route('platform.login') }}">Owner login</a>
    </main>
</body>
</html>
