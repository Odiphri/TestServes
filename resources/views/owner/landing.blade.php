@extends('owner.layout')

@section('title', 'School Portal SaaS')

@section('body')
<div class="owner-public">
    <header class="owner-nav">
        <a class="owner-logo" href="{{ route('platform.home') }}">
            <span class="owner-logo-mark">TS</span>
            <span>TestServes</span>
        </a>
        <div class="owner-nav-actions">
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('platform.login') }}">Owner login</a>
            <a class="btn btn-primary btn-sm" href="{{ route('platform.register') }}">Start free</a>
        </div>
    </header>

    <main class="landing-hero">
        <section class="landing-copy">
            <span class="owner-eyebrow">CBT and school portal SaaS</span>
            <h1>Bring your school online without disturbing your current CBT users.</h1>
            <p>TestServes gives school owners a clean onboarding workspace to register a school, choose a plan, prepare branding, and track portal approval before staff and students enter the CBT environment.</p>
            <div class="landing-actions">
                <a class="btn btn-primary btn-lg" href="{{ route('platform.register') }}">Create school portal</a>
                <a class="btn btn-outline-secondary btn-lg" href="{{ route('platform.login') }}">Owner login</a>
            </div>
            <div class="landing-metrics">
                <div><strong>1</strong><span>Owner workspace</span></div>
                <div><strong>4</strong><span>Setup stages</span></div>
                <div><strong>0</strong><span>CBT disruption</span></div>
            </div>
        </section>

        <section class="landing-product" aria-label="TestServes portal preview">
            <div class="product-window">
                <div class="product-bar">
                    <span></span><span></span><span></span>
                    <strong>Owner Dashboard</strong>
                </div>
                <div class="product-grid">
                    <div class="product-main">
                        <div class="product-header">
                            <div>
                                <span>School profile</span>
                                <h2>Greenfield Academy</h2>
                            </div>
                            <em>Pending approval</em>
                        </div>
                        <div class="product-progress">
                            <div style="width: 68%"></div>
                        </div>
                        <div class="product-steps">
                            <div class="done">Owner account created</div>
                            <div class="done">School profile saved</div>
                            <div class="active">Branding review</div>
                            <div>Portal activation</div>
                        </div>
                    </div>
                    <div class="product-side">
                        <div>
                            <span>Plan</span>
                            <strong>Standard</strong>
                        </div>
                        <div>
                            <span>Portal URL</span>
                            <strong>Reserved</strong>
                        </div>
                        <div>
                            <span>Payment</span>
                            <strong>Manual review</strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <section class="landing-section">
        <div class="section-heading">
            <span class="owner-eyebrow">How it works</span>
            <h2>Separate public signup from the school CBT portal.</h2>
        </div>
        <div class="landing-cards">
            <article>
                <strong>1</strong>
                <h3>School owner signs up</h3>
                <p>Owners create a SaaS account, submit school details, and reserve a portal name.</p>
            </article>
            <article>
                <strong>2</strong>
                <h3>TestServes reviews setup</h3>
                <p>Super Admins confirm plan, payment records, branding, and onboarding status.</p>
            </article>
            <article>
                <strong>3</strong>
                <h3>CBT opens after setup</h3>
                <p>Staff and student CBT access remains separate from the public owner journey.</p>
            </article>
        </div>
    </section>
</div>
@endsection
