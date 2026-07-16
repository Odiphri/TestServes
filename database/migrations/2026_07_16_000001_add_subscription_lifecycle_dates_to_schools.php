<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schools')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'next_payment_due_at')) {
                $table->date('next_payment_due_at')->nullable()->after('subscription_expires_at');
            }

            if (! Schema::hasColumn('schools', 'payment_grace_ends_at')) {
                $table->date('payment_grace_ends_at')->nullable()->after('next_payment_due_at');
            }

            if (! Schema::hasColumn('schools', 'deactivation_scheduled_at')) {
                $table->timestamp('deactivation_scheduled_at')->nullable()->after('payment_grace_ends_at');
            }

            if (! Schema::hasColumn('schools', 'last_payment_failed_at')) {
                $table->timestamp('last_payment_failed_at')->nullable()->after('deactivation_scheduled_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('schools')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            foreach (['last_payment_failed_at', 'deactivation_scheduled_at', 'payment_grace_ends_at', 'next_payment_due_at'] as $column) {
                if (Schema::hasColumn('schools', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
