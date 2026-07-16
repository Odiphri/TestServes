<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Support\SubscriptionLifecycleService;
use Illuminate\Console\Command;

class EnforceSubscriptionLifecycle extends Command
{
    protected $signature = 'subscriptions:enforce {--dry-run : Show affected schools without saving changes}';

    protected $description = 'Move expired schools through grace period and deactivation rules.';

    public function handle(SubscriptionLifecycleService $lifecycle): int
    {
        $schools = School::query()
            ->whereIn('status', ['active', 'trial', 'expired'])
            ->whereNotNull('subscription_expires_at')
            ->whereDate('subscription_expires_at', '<', now()->toDateString())
            ->orderBy('id')
            ->get();

        if ($this->option('dry-run')) {
            $schools->each(function (School $school): void {
                $this->line("Would check {$school->name} ({$school->status}, expires {$school->subscription_expires_at?->format('Y-m-d')})");
            });

            $this->info("Dry run complete. {$schools->count()} expired school(s) found.");

            return self::SUCCESS;
        }

        $expired = 0;
        $deactivated = 0;

        foreach ($schools as $school) {
            $before = $school->status;
            $after = $lifecycle->refresh($school)->status;

            if ($before !== $after && $after === 'expired') {
                $expired++;
            }

            if ($before !== $after && $after === 'deactivated') {
                $deactivated++;
            }
        }

        $this->info("Checked {$schools->count()} school(s). Marked expired: {$expired}. Deactivated: {$deactivated}.");

        return self::SUCCESS;
    }
}
