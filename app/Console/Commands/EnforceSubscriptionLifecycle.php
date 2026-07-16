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
            ->orderBy('id')
            ->get();

        if ($this->option('dry-run')) {
            $schools->each(function (School $school): void {
                $endsAt = $school->payment_status === 'trial'
                    ? ($school->trial_ends_at ?: $school->subscription_expires_at)
                    : ($school->subscription_ends_at ?: $school->subscription_expires_at);

                $this->line("Would check {$school->name} ({$school->status}, ends {$endsAt?->format('Y-m-d')})");
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
