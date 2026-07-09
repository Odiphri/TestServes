<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Support\TenantDatabaseManager;
use Illuminate\Console\Command;

class ProvisionSchoolTenants extends Command
{
    protected $signature = 'schools:provision-tenants {--school= : Only provision one school slug}';

    protected $description = 'Create and migrate tenant databases for schools.';

    public function handle(TenantDatabaseManager $tenants): int
    {
        $schools = School::query()
            ->when($this->option('school'), fn ($query, $slug) => $query->where('slug', $slug))
            ->orderBy('slug')
            ->get();

        if ($schools->isEmpty()) {
            $this->warn('No schools found.');

            return self::SUCCESS;
        }

        foreach ($schools as $school) {
            $this->line("Provisioning {$school->slug}...");
            $tenants->createAndMigrate($school);
            $this->info("Ready: {$school->tenant_database}");
        }

        return self::SUCCESS;
    }
}
