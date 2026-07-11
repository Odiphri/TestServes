<?php

namespace Tests\Unit;

use App\Models\School;
use App\Support\TenantDatabaseManager;
use Tests\TestCase;

class TenantDatabaseManagerTest extends TestCase
{
    public function test_mysql_configuration_repairs_stale_sqlite_tenant_metadata(): void
    {
        config([
            'database.default' => 'mysql',
            'testserves.tenant_connection' => 'mysql',
            'testserves.tenant_database_prefix' => 'testserves_school_',
        ]);

        $school = new School([
            'name' => 'CYOLE Stars Secondary School',
            'slug' => 'cyole-stars',
            'tenant_connection' => 'sqlite',
            'tenant_database' => database_path('tenants/cyole-stars.sqlite'),
            'tenant_database_created_at' => now(),
        ]);

        app(TenantDatabaseManager::class)->fillTenantMetadata($school);

        $this->assertSame('mysql', $school->tenant_connection);
        $this->assertSame('testserves_school_cyole_stars', $school->tenant_database);
    }
}
