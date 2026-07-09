<?php

return [
    'root_domain' => env('TESTSERVES_ROOT_DOMAIN', 'testserves.com'),
    'domain_aliases' => array_filter(array_map('trim', explode(',', env('TESTSERVES_DOMAIN_ALIASES', 'testservers.com')))),
    'portal_scheme' => env('TESTSERVES_PORTAL_SCHEME', env('APP_ENV') === 'production' ? 'https' : 'http'),
    'tenant_connection' => env('TESTSERVES_TENANT_CONNECTION', 'sqlite'),
    'tenant_sqlite_path' => env('TESTSERVES_TENANT_SQLITE_PATH', database_path('tenants')),
    'tenant_database_prefix' => env('TESTSERVES_TENANT_DATABASE_PREFIX', 'testserves_school_'),
];
