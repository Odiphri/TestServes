<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TestServesDomains
{
    public static function rootDomain(): string
    {
        return Str::lower(trim((string) config('testserves.root_domain', 'testserves.com')));
    }

    public static function portalUrl(string $schoolSlug): string
    {
        $slug = Str::slug($schoolSlug);
        $scheme = config('testserves.portal_scheme', 'https');

        return "{$scheme}://{$slug}.".self::rootDomain();
    }

    public static function schoolSlugFromRequest(Request $request): ?string
    {
        return self::schoolSlugFromHost($request->getHost());
    }

    public static function schoolSlugFromHost(string $host): ?string
    {
        $host = Str::lower(preg_replace('/:\d+$/', '', trim($host)));

        foreach (self::allRootDomains() as $root) {
            if ($host === $root || $host === "www.{$root}" || ! Str::endsWith($host, ".{$root}")) {
                continue;
            }

            $subdomain = Str::beforeLast($host, ".{$root}");

            if (filled($subdomain) && ! str_contains($subdomain, '.')) {
                return $subdomain;
            }
        }

        return null;
    }

    public static function schoolLoginUrl(Request $request): string
    {
        if (app()->runningUnitTests() || app()->environment('testing')) {
            return route('login');
        }

        if (self::schoolSlugFromRequest($request)) {
            return $request->getSchemeAndHttpHost().'/login';
        }

        return route('platform.login');
    }

    public static function allRootDomains(): array
    {
        return array_values(array_unique(array_filter(array_merge(
            [self::rootDomain()],
            array_map(fn ($domain) => Str::lower($domain), config('testserves.domain_aliases', []))
        ))));
    }
}
