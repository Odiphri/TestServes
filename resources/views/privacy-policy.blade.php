<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $portalSchool = $currentSchool ?? null;
        $schoolName = $portalSchool?->branding?->portal_display_name ?? $portalSchool?->name ?? $schoolSettings?->school_name ?? 'TestServes';
        $schoolIcon = $portalSchool?->branding?->logo_url ?? $schoolSettings?->logo_url ?? \App\Models\SystemSetting::platformLogoUrl();
        $contactEmail = 'info@testserves.com';
    @endphp
    <title>Privacy Policy - {{ $schoolName }} CBT Portal</title>
    <link rel="icon" href="{{ $schoolIcon }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ $schoolIcon }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0B1F5B;
            --primary-dark: #081645;
            --accent: #1E88FF;
            --accent-light: #4DA3FF;
            --background: #F8FAFC;
            --surface: #FFFFFF;
            --text: #111827;
            --text-secondary: #6B7280;
        }
        
        body {
            background: linear-gradient(135deg, var(--background) 0%, #E0E7FF 100%);
            color: var(--text);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }

        .policy-wrap {
            max-width: 920px;
            margin: 0 auto;
            padding: 40px 16px;
        }

        .policy-card {
            background: var(--surface);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .policy-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: var(--surface);
            padding: 36px;
        }

        .policy-body {
            padding: 36px;
        }

        h1 {
            font-size: clamp(1.8rem, 4vw, 2.4rem);
            font-weight: 700;
            margin: 0;
        }

        h2 {
            font-size: 1.15rem;
            font-weight: 700;
            margin-top: 28px;
            color: var(--primary);
        }

        p, li {
            line-height: 1.8;
            color: var(--text-secondary);
        }

        .back-link {
            color: var(--accent);
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: var(--primary);
        }

        a {
            color: var(--accent);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        a:hover {
            color: var(--primary);
        }

        @media (max-width: 576px) {
            .policy-header,
            .policy-body {
                padding: 24px;
            }
            
            .policy-wrap {
                padding: 24px 16px;
            }
        }
    </style>
</head>
<body>
    <main class="policy-wrap">
        <div class="policy-card">
            <div class="policy-header">
                <h1>Privacy Policy</h1>
                <p class="mb-0 mt-2">{{ $schoolName }} CBT Portal</p>
            </div>
            <div class="policy-body">
                <p>
                    This policy explains how {{ $schoolName }} handles information used in the CBT Portal.
                    The portal is provided for school examination, learning, and administration.
                </p>

                <h2>Information We Collect</h2>
                <p>
                    We collect account and school records needed to identify and support users, such as names,
                    portal IDs, class information, contact details where provided, login records, profile details,
                    attendance, payment status, and role or permission information.
                </p>

                <h2>Examination Records and Scores</h2>
                <p>
                    When students take exams, the portal stores exam attempts, selected answers, start and submission
                    times, scores, percentages, grades, and related subject or class records. These records are kept
                    securely in the school system so authorized staff can manage assessment, reporting, and academic
                    follow-up.
                </p>

                <h2>How We Use Data</h2>
                <p>
                    User data is used only for educational and administrative purposes, including account access,
                    exam delivery, result processing, class management, attendance, fee administration, support,
                    audit, and school reporting.
                </p>

                <h2>Access to Student Data</h2>
                <p>
                    Unauthorized third parties do not have access to student data. Access is limited to approved school
                    users whose roles require the information, such as administrators, teachers, HODs, CBT personnel,
                    or other authorized staff.
                </p>

                <h2>Data Concerns</h2>
                <p>
                    For questions, corrections, or concerns about personal data or examination records, contact the
                    school administration at <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a> or by phone at
                    <a href="tel:+2348186519024">+234 818 651 9024</a>.
                </p>

                <h2>Contact Us on WhatsApp</h2>
                <p>
                    You can also reach us quickly on WhatsApp: <a href="https://wa.me/2348186519024?text=Hello%20TestServes" target="_blank" rel="noopener">WhatsApp +234 818 651 9024</a>.
                </p>

                <div class="mt-4">
                    <a href="{{ route('platform.login') }}" class="back-link text-decoration-none">Back to Login</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
