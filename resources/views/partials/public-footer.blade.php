@php
    $settings = \App\Support\PublicSiteSettings::all();
    $platformName = $settings['platform_name'] ?? 'TestServes';
    $operatorStatement = $settings['legal_operator_statement'] ?? \App\Support\PublicSiteSettings::OPERATOR_STATEMENT;
    $links = [
        'platform' => [
            ['About', route('platform.home').'#features'],
            ['Pricing', route('platform.home').'#features'],
            ['Contact Us', route('contact')],
            ['Help Centre/FAQs', route('live-support.create')],
        ],
        'legal' => [
            ['Privacy Policy', route('privacy.policy')],
            ['Terms of Service', route('terms.service')],
            ['Cookie Policy', route('cookie.policy')],
            ['Refund Policy', route('refund.policy')],
            ['Data Protection', route('data.protection')],
        ],
    ];
@endphp

<style>
    .ts-public-footer {
        border-top: 1px solid #dbe3ef;
        background: #102033;
        color: #d9e8f7;
        padding: 38px 0 22px;
    }
    .ts-public-footer a { color: #e6fffb; text-decoration: none; font-weight: 700; }
    .ts-public-footer a:hover { color: #99f6e4; }
    .ts-public-footer-inner {
        width: min(1180px, calc(100% - 32px));
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(220px, 1.2fr) repeat(3, minmax(160px, .8fr));
        gap: 22px;
    }
    .ts-public-footer h2,
    .ts-public-footer h3 {
        color: #fff;
        font-size: 16px;
        font-weight: 800;
        margin: 0 0 12px;
    }
    .ts-public-footer p,
    .ts-public-footer li,
    .ts-public-footer small {
        color: #b7c8d9;
        line-height: 1.65;
    }
    .ts-public-footer ul { list-style: none; padding: 0; margin: 0; display: grid; gap: 8px; }
    .ts-footer-bottom {
        width: min(1180px, calc(100% - 32px));
        margin: 24px auto 0;
        padding-top: 18px;
        border-top: 1px solid rgba(255,255,255,.12);
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    @media (max-width: 820px) {
        .ts-public-footer-inner { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 560px) {
        .ts-public-footer-inner { grid-template-columns: 1fr; }
    }
</style>

<footer class="ts-public-footer">
    <div class="ts-public-footer-inner">
        <section>
            <h2>{{ $platformName }}</h2>
            <p>School-focused CBT and assessment management built for modern schools.</p>
            <small>{{ $operatorStatement }}</small>
        </section>
        <section>
            <h3>Platform</h3>
            <ul>
                @foreach($links['platform'] as [$label, $href])
                    <li><a href="{{ $href }}">{{ $label }}</a></li>
                @endforeach
            </ul>
        </section>
        <section>
            <h3>Legal</h3>
            <ul>
                @foreach($links['legal'] as [$label, $href])
                    <li><a href="{{ $href }}">{{ $label }}</a></li>
                @endforeach
            </ul>
        </section>
        <section>
            <h3>Contact & Social</h3>
            <ul>
                <li><a href="{{ $settings['whatsapp_support_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Contact TestServes on WhatsApp">WhatsApp Support</a></li>
                <li><a href="{{ $settings['whatsapp_community_url'] }}" target="_blank" rel="noopener noreferrer">Join WhatsApp Community</a></li>
                <li><a href="{{ $settings['x_url'] }}" target="_blank" rel="noopener noreferrer">Follow us on X {{ $settings['x_handle'] }}</a></li>
                <li><a href="{{ route('contact') }}">Contact Us</a></li>
            </ul>
        </section>
    </div>
    <div class="ts-footer-bottom">
        <small>&copy; {{ now()->year }} {{ $platformName }}. All rights reserved.</small>
        @if(filled($settings['company_registration_number'] ?? null))
            <small>{{ $settings['company_registration_label'] ?: 'Registration number' }}: {{ $settings['company_registration_number'] }}</small>
        @endif
    </div>
</footer>
