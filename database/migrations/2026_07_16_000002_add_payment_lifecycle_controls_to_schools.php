<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schools')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('subscription_expires_at');
            }

            if (! Schema::hasColumn('schools', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable()->after('trial_ends_at');
            }

            if (! Schema::hasColumn('schools', 'payment_status')) {
                $table->string('payment_status', 40)->default('pending')->after('subscription_status')->index();
            }

            if (! Schema::hasColumn('schools', 'last_payment_at')) {
                $table->timestamp('last_payment_at')->nullable()->after('last_payment_failed_at');
            }

            if (! Schema::hasColumn('schools', 'auto_renew')) {
                $table->boolean('auto_renew')->default(false)->after('last_payment_at');
            }

            if (! Schema::hasColumn('schools', 'grace_period_days')) {
                $table->unsignedSmallInteger('grace_period_days')->nullable()->after('auto_renew');
            }

            if (! Schema::hasColumn('schools', 'portal_locked')) {
                $table->boolean('portal_locked')->default(false)->after('grace_period_days')->index();
            }
        });

        DB::table('schools')->orderBy('id')->chunkById(200, function ($schools): void {
            foreach ($schools as $school) {
                $paymentStatus = match ($school->status) {
                    'active' => 'paid',
                    'trial' => 'trial',
                    'expired' => 'expired',
                    'suspended' => 'suspended',
                    'deactivated' => 'deactivated',
                    default => 'pending',
                };

                DB::table('schools')->where('id', $school->id)->update([
                    'payment_status' => $paymentStatus,
                    'trial_ends_at' => $school->status === 'trial' ? $school->subscription_expires_at : null,
                    'subscription_ends_at' => $school->status === 'active' ? $school->subscription_expires_at : null,
                    'portal_locked' => in_array($school->status, ['expired', 'suspended', 'deactivated'], true),
                ]);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('schools')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            foreach (['portal_locked', 'grace_period_days', 'auto_renew', 'last_payment_at', 'payment_status', 'subscription_ends_at', 'trial_ends_at'] as $column) {
                if (Schema::hasColumn('schools', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
