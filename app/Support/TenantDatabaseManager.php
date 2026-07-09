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
        $this->createDatabaseFileIfNeeded($school);
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
        $connection = $school->tenant_connection ?: config('testserves.tenant_connection', 'sqlite');
        $database = $school->tenant_database ?: $this->databaseNameFor($school, $connection);

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

    private function createDatabaseFileIfNeeded(School $school): void
    {
        if ($school->tenant_connection !== 'sqlite') {
            return;
        }

        $directory = dirname($school->tenant_database);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (! File::exists($school->tenant_database)) {
            File::put($school->tenant_database, '');
        }
    }

    private function databaseExists(School $school): bool
    {
        $this->fillTenantMetadata($school);

        if ($school->tenant_connection === 'sqlite') {
            return File::exists($school->tenant_database);
        }

        return filled($school->tenant_database);
    }

    private function sqlitePath(string $slug): string
    {
        return rtrim(config('testserves.tenant_sqlite_path'), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR.$slug.'.sqlite';
    }
}
