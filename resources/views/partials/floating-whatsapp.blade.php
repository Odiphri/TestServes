@php($whatsappUrl = \App\Support\PublicSiteSettings::get('whatsapp_support_url'))
@if($whatsappUrl)
    <style>
        .ts-whatsapp-float {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 60;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            color: #fff;
            background: #128c7e;
            box-shadow: 0 16px 36px rgba(18, 140, 126, .32);
            text-decoration: none;
            font-weight: 800;
        }
        .ts-whatsapp-float:hover { color: #fff; background: #0f766e; }
        @media (max-width: 576px) {
            .ts-whatsapp-float { right: 14px; bottom: 14px; width: 48px; height: 48px; }
        }
    </style>
    <a class="ts-whatsapp-float" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Contact TestServes on WhatsApp">WA</a>
@endif
