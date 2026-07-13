@unless(request()->cookie('testserves_cookie_acknowledged'))
    <style>
        .ts-cookie-notice {
            position: fixed;
            left: 16px;
            right: 86px;
            bottom: 16px;
            z-index: 55;
            max-width: 760px;
            background: #fff;
            border: 1px solid #dbe3ef;
            border-radius: 8px;
            box-shadow: 0 16px 40px rgba(16,32,51,.18);
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .ts-cookie-notice p { margin: 0; color: #4b5f78; line-height: 1.5; font-size: 14px; }
        .ts-cookie-notice button { white-space: nowrap; border: 0; border-radius: 8px; padding: 8px 12px; background: #0f766e; color: #fff; font-weight: 800; }
        @media (max-width: 680px) {
            .ts-cookie-notice { right: 16px; bottom: 74px; align-items: flex-start; flex-direction: column; }
        }
    </style>
    <div class="ts-cookie-notice" id="tsCookieNotice" role="status">
        <p>TestServes uses essential cookies for sessions, security and preferences. See our <a href="{{ route('cookie.policy') }}">Cookie Policy</a>.</p>
        <button type="button" id="tsCookieAccept">OK</button>
    </div>
    <script>
        document.getElementById('tsCookieAccept')?.addEventListener('click', () => {
            document.cookie = 'testserves_cookie_acknowledged=1; path=/; max-age=31536000; samesite=lax';
            document.getElementById('tsCookieNotice')?.remove();
        });
    </script>
@endunless
