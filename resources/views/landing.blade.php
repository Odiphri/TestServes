<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $platformName = \App\Models\SystemSetting::platformName();
        $platformLogo = \App\Models\SystemSetting::platformLogoUrl() ?: asset('images/tslogo.jpeg');
        $seoTitle = 'TestServes - CBT and School Portal SaaS';
        $seoDescription = 'TestServes — the CBT platform that lets schools create, conduct, and grade exams online. No paper, no marking stress, instant results. Built for Nigerian schools that want to go digital.';
        $canonicalUrl = 'https://testserves.com';
        $previewImage = 'https://testserves.com/images/tslogo.jpeg';
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="TestServes — the CBT platform that lets schools create, conduct, and grade exams online. No paper, no marking stress, instant results. Built for Nigerian schools that want to go digital.">
    <meta name="keywords" content="TestServes, CBT software, school portal, online exams, school management SaaS">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:image" content="{{ $previewImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TestServes">
    <meta property="og:locale" content="en_NG">
    <meta property="og:locale:alternate" content="en_US">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $previewImage }}">
    <meta name="twitter:site" content="@testserves">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" href="{{ $platformLogo }}">
    <link rel="apple-touch-icon" href="{{ $platformLogo }}">
    @verbatim
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "TestServes",
      "alternateName": "TestServes CBT",
      "description": "CBT and School Portal SaaS for managing school exams, student assessments, and branded school portals in Nigeria",
      "url": "https://testserves.com",
      "applicationCategory": "EducationApplication",
      "operatingSystem": "Web",
      "softwareVersion": "1.0",
      "offers": {
        "@type": "Offer",
        "name": "Standard Plan",
        "price": "0",
        "priceCurrency": "NGN",
        "availability": "https://schema.org/InStock"
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.8",
        "ratingCount": "50"
      },
      "publisher": {
        "@type": "Organization",
        "name": "TestServes",
        "url": "https://testserves.com",
        "logo": {
          "@type": "ImageObject",
          "url": "https://testserves.com/images/tslogo.jpeg"
        },
        "contactPoint": {
          "@type": "ContactPoint",
          "email": "testserves.ng@gmail.com",
          "contactType": "customer support",
          "availableLanguage": ["English"]
        }
      },
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "https://testserves.com"
      }
    }
    </script>

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "TestServes",
      "url": "https://testserves.com",
      "logo": "https://testserves.com/images/tslogo.jpeg",
      "sameAs": [],
      "contactPoint": {
        "@type": "ContactPoint",
        "email": "testserves.ng@gmail.com",
        "contactType": "customer support"
      }
    }
    </script>

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "TestServes",
      "url": "https://testserves.com",
      "potentialAction": {
        "@type": "SearchAction",
        "target": {
          "@type": "EntryPoint",
          "urlTemplate": "https://testserves.com/search?q={search_term_string}"
        },
        "query-input": "required name=search_term_string"
      }
    }
    </script>
    @endverbatim
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --ink: #102033;
            --muted: #607086;
            --line: #dce5ee;
            --bg: #f6f9fc;
            --teal: #0f766e;
            --teal-dark: #0b5f59;
            --gold: #f59e0b;
            --panel: #ffffff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: var(--ink);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(15,118,110,.16), transparent 340px),
                linear-gradient(180deg, #ffffff 0%, var(--bg) 45%, #ffffff 100%);
        }
        a { text-decoration: none; }
        .site-nav {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 18px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--ink);
            font-weight: 800;
            font-size: 18px;
        }
        .brand-mark {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            color: #fff;
            background: var(--ink);
            box-shadow: 0 12px 28px rgba(16,32,51,.18);
        }
        .brand-logo-img {
            width: 36px;
            height: 36px;
            object-fit: contain;
            border-radius: 8px;
            background: #fff;
            padding: 3px;
            box-shadow: 0 12px 28px rgba(16,32,51,.18);
            flex: 0 0 auto;
        }
        .nav-links {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .nav-links a {
            color: var(--ink);
            font-weight: 700;
            font-size: 14px;
        }
        .btn-main {
            background: var(--teal);
            border-color: var(--teal);
            color: #fff !important;
            font-weight: 800;
            box-shadow: 0 14px 28px rgba(15,118,110,.22);
        }
        .btn-main:hover { background: var(--teal-dark); border-color: var(--teal-dark); }
        .btn-soft {
            border: 1px solid var(--line);
            background: rgba(255,255,255,.82);
            color: var(--ink) !important;
            font-weight: 800;
        }
        .hero {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 54px 0 42px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, .92fr);
            gap: 36px;
            align-items: center;
        }
        .eyebrow {
            display: inline-flex;
            width: fit-content;
            color: #115e59;
            background: #e6fffb;
            border: 1px solid #b7ece6;
            border-radius: 999px;
            padding: 7px 11px;
            font-size: 13px;
            font-weight: 800;
        }
        .hero h1 {
            max-width: 800px;
            margin: 18px 0;
            font-size: clamp(46px, 7vw, 78px);
            line-height: .96;
            font-weight: 800;
            letter-spacing: 0;
        }
        .hero p {
            max-width: 690px;
            color: var(--muted);
            font-size: 18px;
            line-height: 1.75;
            margin: 0;
        }
        .hero-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 28px;
        }
        .hero-points {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 30px;
        }
        .hero-points div {
            background: rgba(255,255,255,.86);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 14px;
            box-shadow: 0 12px 30px rgba(16,32,51,.06);
        }
        .hero-points strong {
            display: block;
            font-size: 23px;
            line-height: 1;
        }
        .hero-points span {
            display: block;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
            margin-top: 6px;
        }
        .product {
            background: #102033;
            color: #d9e8f7;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,.08);
            overflow: hidden;
            box-shadow: 0 34px 86px rgba(16,32,51,.24);
        }
        .product-top {
            padding: 15px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .dots { display: flex; gap: 7px; }
        .dots span {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #94a3b8;
        }
        .dots span:first-child { background: #f87171; }
        .dots span:nth-child(2) { background: #fbbf24; }
        .dots span:nth-child(3) { background: #34d399; }
        .product-body {
            padding: 16px;
            display: grid;
            gap: 14px;
        }
        .portal-card {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.09);
            border-radius: 8px;
            padding: 16px;
        }
        .portal-head {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: flex-start;
        }
        .portal-head span,
        .portal-grid span {
            color: #94a3b8;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .portal-head h2 {
            color: #fff;
            margin: 6px 0 0;
            font-size: 28px;
        }
        .status {
            background: rgba(245,158,11,.18);
            color: #fde68a;
            border: 1px solid rgba(253,230,138,.22);
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }
        .progress-line {
            height: 10px;
            margin: 20px 0;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(255,255,255,.1);
        }
        .progress-line div {
            width: 68%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #14b8a6, var(--gold));
        }
        .portal-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }
        .portal-grid div {
            background: rgba(255,255,255,.055);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 8px;
            padding: 12px;
        }
        .portal-grid strong {
            display: block;
            color: #fff;
            margin-top: 7px;
        }
        .section {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 46px 0;
        }
        .section h2 {
            font-size: clamp(30px, 4vw, 44px);
            font-weight: 800;
            margin: 12px 0 8px;
        }
        .section-lead {
            max-width: 720px;
            color: var(--muted);
            font-size: 17px;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }
        .feature {
            background: rgba(255,255,255,.9);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 14px 34px rgba(16,32,51,.07);
        }
        .feature strong {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            background: #e6fffb;
            color: #115e59;
            border-radius: 8px;
            margin-bottom: 14px;
        }
        .feature h3 {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .feature p {
            color: var(--muted);
            line-height: 1.65;
            margin: 0;
        }
        .cta-band {
            width: min(1180px, calc(100% - 32px));
            margin: 22px auto 64px;
            background: var(--ink);
            color: #fff;
            border-radius: 8px;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
        }
        .cta-band p {
            color: #cbd5e1;
            margin: 6px 0 0;
        }
        @media (max-width: 900px) {
            .hero,
            .feature-grid,
            .portal-grid {
                grid-template-columns: 1fr;
            }
            .hero-points {
                grid-template-columns: 1fr;
            }
            .cta-band {
                align-items: flex-start;
                flex-direction: column;
            }
        }
        @media (max-width: 576px) {
            .site-nav {
                align-items: flex-start;
                flex-direction: column;
            }
            .nav-links {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <header class="site-nav">
        <a class="brand" href="{{ route('platform.home') }}">
            @if($platformLogo)
                <img class="brand-logo-img" src="{{ $platformLogo }}" alt="{{ $platformName }}" onerror="this.style.display='none';this.nextElementSibling.style.display='grid';">
                <span class="brand-mark" style="display:none;">TS</span>
            @else
                <span class="brand-mark">TS</span>
            @endif
            <span>{{ $platformName }}</span>
        </a>
        <nav class="nav-links" aria-label="Primary navigation">
            <a href="#features">Features</a>
            <a href="#how-it-works">How it works</a>
            <a href="{{ route('live-support.create') }}">Live support</a>
            <a class="btn btn-soft btn-sm" href="{{ route('platform.login') }}">Owner login</a>
            <a class="btn btn-main btn-sm" href="{{ route('platform.register') }}">Start onboarding</a>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div>
                <span class="eyebrow">School portal and CBT SaaS</span>
                <h1>Set up your school portal before opening CBT access.</h1>
                <p>{{ $platformName }} helps school owners register their school, choose a plan, prepare branding, and track approval while keeping staff and student CBT login separate.</p>
                <div class="hero-actions">
                    <a class="btn btn-main btn-lg" href="{{ route('platform.register') }}">Create school portal</a>
                    <a class="btn btn-soft btn-lg" href="{{ route('platform.login') }}">Owner login</a>
                </div>
                <div class="hero-points">
                    <div><strong>Owner first</strong><span>Signup starts with the school owner, not CBT users.</span></div>
                    <div><strong>Portal ready</strong><span>Branding and plan setup happen before launch.</span></div>
                    <div><strong>CBT separate</strong><span>Existing school login stays protected and separate.</span></div>
                </div>
            </div>

            <div class="product" aria-label="Product preview">
                <div class="product-top">
                    <div class="dots"><span></span><span></span><span></span></div>
                    <strong>Owner workspace preview</strong>
                </div>
                <div class="product-body">
                    <div class="portal-card">
                        <div class="portal-head">
                            <div>
                                <span>School profile</span>
                                <h2>Greenfield Academy</h2>
                            </div>
                            <div class="status">Pending approval</div>
                        </div>
                        <div class="progress-line"><div></div></div>
                        <div class="portal-grid">
                            <div><span>Plan</span><strong>Standard</strong></div>
                            <div><span>Portal URL</span><strong>Reserved</strong></div>
                            <div><span>Payment</span><strong>Manual review</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="features">
            <span class="eyebrow">Platform features</span>
            <h2>Everything starts with a clean school owner journey.</h2>
            <p class="section-lead">The public site explains the platform. Owner login and signup live on their own pages. Staff and students only touch CBT after the school setup exists.</p>
            <div class="feature-grid">
                <article class="feature">
                    <strong>1</strong>
                    <h3>Owner onboarding</h3>
                    <p>School owners create their account, enter school details, reserve a slug, and choose a plan.</p>
                </article>
                <article class="feature">
                    <strong>2</strong>
                    <h3>Super Admin management</h3>
                    <p>Platform staff review schools, owners, payments, support tickets, and settings.</p>
                </article>
                <article class="feature">
                    <strong>3</strong>
                    <h3>CBT after setup</h3>
                    <p>The CBT login is separate and only useful after the school has been created and prepared.</p>
                </article>
            </div>
        </section>

        <section class="section" id="how-it-works">
            <span class="eyebrow">How it works</span>
            <h2>Public page, owner account, then school portal.</h2>
            <div class="feature-grid">
                <article class="feature">
                    <strong>A</strong>
                    <h3>Visitor learns about TestServes</h3>
                    <p>The homepage introduces the SaaS platform, features, and the right next step.</p>
                </article>
                <article class="feature">
                    <strong>B</strong>
                    <h3>Owner signs up or logs in</h3>
                    <p>Login and signup pages are focused auth screens, separate from the marketing page.</p>
                </article>
                <article class="feature">
                    <strong>C</strong>
                    <h3>School gets activated later</h3>
                    <p>Portal connection and automation can come in future phases without changing the public flow.</p>
                </article>
            </div>
        </section>

        <section class="cta-band">
            <div>
                <h2 class="h3 mb-0">Ready to start a school portal?</h2>
                <p>Create the owner workspace first. CBT access stays separate.</p>
            </div>
            <a class="btn btn-main btn-lg" href="{{ route('platform.register') }}">Start onboarding</a>
        </section>
    </main>
    @include('partials.public-footer')
    @include('partials.floating-whatsapp')
    @include('partials.cookie-notice')
</body>
</html>
