<?php

namespace App\Support;

use App\Models\School;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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

    public function createAndMigrate(School $school): void
    {
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
    }

    public function fillTenantMetadata(School $school): void
    {
        $configuredConnection = config('testserves.tenant_connection', config('database.default', 'sqlite'));
        $connection = $school->tenant_connection ?: $configuredConnection;

        if (! $school->tenant_database_created_at && $connection !== $configuredConnection) {
            $connection = $configuredConnection;
        }

        $database = $school->tenant_database;

        if (
            blank($database)
            || (! $school->tenant_database_created_at && $school->tenant_connection !== $connection)
            || (! $school->tenant_database_created_at && $connection !== 'sqlite' && $this->looksLikeSqlitePath($database))
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
        $connection ??= config('testserves.tenant_connection', 'sqlite');
        $slug = Str::slug($school->slug, '_');

        if ($connection === 'sqlite') {
            return $this->sqlitePath($slug);
        }

        return config('testserves.tenant_database_prefix', 'testserves_school_').$slug;
    }

    private function configureConnection(School $school): void
    {
        $this->fillTenantMetadata($school);

        $baseConnection = config('database.connections.'.$school->tenant_connection);
        $baseConnection['database'] = $school->tenant_database;

        config(['database.connections.tenant' => $baseConnection]);
        DB::purge('tenant');
    }

    private function createDatabaseIfNeeded(School $school): void
    {
        if ($school->tenant_connection === 'sqlite') {
            $directory = dirname($school->tenant_database);

            if (! File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            if (! File::exists($school->tenant_database)) {
                File::put($school->tenant_database, '');
            }

            return;
        }

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
        }
    }

    private function databaseExists(School $school): bool
    {
        $this->fillTenantMetadata($school);

        if ($school->tenant_connection === 'sqlite') {
            return File::exists($school->tenant_database);
        }

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

        return false;
    }

    private function sqlitePath(string $slug): string
    {
        return rtrim(config('testserves.tenant_sqlite_path'), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR.$slug.'.sqlite';
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
}
