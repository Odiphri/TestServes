<?php

namespace App\Support;

use App\Models\School;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantDatabaseManager
{
    public function ensureDatabase(School $school): void
    {
        if (! $school->tenant_database_created_at || ! $this->databaseExists($school)) {
            $this->createAndMigrate($school);
        }
    }

    public function activate(School $school): void
    {
        $this->ensureDatabase($school);
        $this->configureConnection($school);
        DB::setDefaultConnection('tenant');
    }

    public function activateExisting(School $school): void
    {
        $this->fillTenantMetadata($school);
        $this->configureConnection($school);
        DB::setDefaultConnection('tenant');
    }

    public function createAndMigrate(School $school): void
    {
        $school->loadMissing(['owner', 'branding', 'plan']);
        $this->fillTenantMetadata($school);
        $this->createDatabaseIfNeeded($school);
        $this->configureConnection($school);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--force' => true,
        ]);

        $school->forceFill([
            'tenant_connection' => $school->tenant_connection,
            'tenant_database' => $school->tenant_database,
            'tenant_database_created_at' => now(),
        ])->save();

        $this->syncTenantBootstrapData($school->fresh(['owner', 'branding', 'plan']));
    }

    public function syncExistingTenant(School $school): void
    {
        if (! $school->tenant_database_created_at || ! $this->databaseExists($school)) {
            return;
        }

        $school->loadMissing(['owner', 'branding', 'plan']);
        $this->configureConnection($school);
        $this->syncTenantBootstrapData($school);
    }

    public function fillTenantMetadata(School $school): void
    {
        $configuredConnection = $this->configuredConnection();
        $connection = $school->tenant_connection ?: $configuredConnection;

        if ($connection !== $configuredConnection) {
            $connection = $configuredConnection;
        }

        $database = $school->tenant_database;

        if (
            blank($database)
            || $school->tenant_connection !== $connection
            || ($connection !== 'sqlite' && $this->looksLikeSqlitePath($database))
        ) {
            $database = $this->databaseNameFor($school, $connection);
        }

        $school->forceFill([
            'tenant_connection' => $connection,
            'tenant_database' => $database,
        ]);
    }

    public function databaseNameFor(School $school, ?string $connection = null): string
    {
        $connection ??= $this->configuredConnection();
        $slug = Str::slug($school->slug, '_');

        return config('testserves.tenant_database_prefix', 'testserves_school_').$slug;
    }

    private function configureConnection(School $school): void
    {
        $this->fillTenantMetadata($school);

        $baseConnection = config('database.connections.'.$school->tenant_connection);
        if (! is_array($baseConnection)) {
            throw new \InvalidArgumentException("Unsupported tenant connection [{$school->tenant_connection}].");
        }

        $baseConnection['database'] = $school->tenant_database;

        config(['database.connections.tenant' => $baseConnection]);
        DB::purge('tenant');
    }

    private function createDatabaseIfNeeded(School $school): void
    {
        if ($school->tenant_connection === 'mysql') {
            $database = $this->safeMysqlDatabaseName($school->tenant_database);
            $connection = config('database.connections.mysql');
            $charset = $connection['charset'] ?? 'utf8mb4';
            $collation = $connection['collation'] ?? 'utf8mb4_unicode_ci';

            $adminConnection = $connection;
            $adminConnection['database'] = null;

            config(['database.connections.tenant_admin' => $adminConnection]);
            DB::purge('tenant_admin');
            DB::connection('tenant_admin')->statement("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET {$charset} COLLATE {$collation}");

            return;
        }

        throw new \InvalidArgumentException("Unsupported tenant connection [{$school->tenant_connection}]. Tenant databases must use mysql.");
    }

    public function databaseExists(School $school): bool
    {
        $this->fillTenantMetadata($school);

        if ($school->tenant_connection === 'mysql') {
            $database = $this->safeMysqlDatabaseName($school->tenant_database);
            $connection = config('database.connections.mysql');
            $adminConnection = $connection;
            $adminConnection['database'] = null;

            config(['database.connections.tenant_admin' => $adminConnection]);
            DB::purge('tenant_admin');

            return DB::connection('tenant_admin')
                ->table('information_schema.SCHEMATA')
                ->where('SCHEMA_NAME', $database)
                ->exists();
        }

        throw new \InvalidArgumentException("Unsupported tenant connection [{$school->tenant_connection}]. Tenant databases must use mysql.");
    }

    private function configuredConnection(): string
    {
        $connection = config('testserves.tenant_connection') ?: config('database.default') ?: 'mysql';

        return $connection === 'sqlite' ? 'mysql' : $connection;
    }

    private function looksLikeSqlitePath(?string $database): bool
    {
        return filled($database) && (
            Str::endsWith($database, '.sqlite')
            || str_contains($database, '/')
            || str_contains($database, '\\')
        );
    }

    private function safeMysqlDatabaseName(string $database): string
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $database)) {
            throw new \InvalidArgumentException('Invalid tenant database name.');
        }

        return $database;
    }

    private function syncTenantBootstrapData(School $school): void
    {
        $branding = $school->branding;
        $owner = $school->owner;
        $features = $school->plan?->features ?? [];

        DB::connection('tenant')->table('school_settings')->updateOrInsert(
            ['id' => 1],
            [
                'school_name' => $branding?->portal_display_name ?: $school->name,
                'logo_path' => $branding?->logo_path,
                'primary_color' => $branding?->primary_color ?: '#0B1F5B',
                'secondary_color' => $branding?->secondary_color ?: '#081645',
                'accent_color' => $branding?->accent_color ?: '#1E88FF',
                'school_address' => $school->address,
                'school_phone' => $school->contact_phone,
                'school_email' => $school->contact_email,
                'enabled_features' => json_encode(array_values($features)),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        if (! $owner || blank($owner->email) || blank($owner->password)) {
            return;
        }

        [$firstName, $lastName] = $this->splitOwnerName($owner->name);

        $payload = [
            'portal_id' => $owner->email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $owner->email,
            'password' => $owner->password,
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
            'updated_at' => now(),
        ];

        $existingId = DB::connection('tenant')->table('users')
            ->where('email', $owner->email)
            ->orWhere('portal_id', $owner->email)
            ->value('id');

        $existingId ??= DB::connection('tenant')->table('users')
            ->where('role', 'admin')
            ->orderBy('id')
            ->value('id');

        if ($existingId) {
            DB::connection('tenant')->table('users')->where('id', $existingId)->update($payload);

            return;
        }

        DB::connection('tenant')->table('users')->insert($payload + ['created_at' => now()]);
    }

    private function splitOwnerName(?string $name): array
    {
        $parts = collect(explode(' ', trim((string) $name)))->filter()->values();
        $firstName = $parts->first() ?: 'School';
        $lastName = $parts->slice(1)->implode(' ') ?: 'Owner';

        return [$firstName, $lastName];
    }
}
