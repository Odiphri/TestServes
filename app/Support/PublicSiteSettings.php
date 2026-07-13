<?php

namespace App\Support;

use App\Models\SystemSetting;

class PublicSiteSettings
{
    public const OPERATOR_STATEMENT = 'TestServes is a digital product operated by Big H Multidynamic Ventures, a CAC-registered business in Nigeria.';

    public static function all(): array
    {
        return array_merge(self::defaults(), array_filter(SystemSetting::values(), fn ($value) => $value !== null && $value !== ''));
    }

    public static function get(string $key): ?string
    {
        return self::all()[$key] ?? null;
    }

    public static function defaults(): array
    {
        return [
            'platform_name' => 'TestServes',
            'support_phone' => '08083019506',
            'contact_phone' => '08083019506',
            'whatsapp_number' => '2348083019506',
            'whatsapp_support_url' => 'https://wa.me/2348083019506?text=Hello%20TestServes%2C%20I%20need%20assistance.',
            'whatsapp_community_url' => 'https://chat.whatsapp.com/CHFm01R1b2n9P1xsZEnFxN',
            'x_url' => 'https://x.com/TestServesng',
            'x_handle' => '@TestServesng',
            'support_email' => 'testserves.ng@gmail.com',
            'contact_email' => 'testserves.ng@gmail.com',
            'website_url' => 'https://testserves.com',
            'legal_operator_name' => 'Big H Multidynamic Ventures',
            'legal_operator_statement' => self::OPERATOR_STATEMENT,
            'company_registration_number' => null,
            'company_registration_label' => null,
        ];
    }
}
