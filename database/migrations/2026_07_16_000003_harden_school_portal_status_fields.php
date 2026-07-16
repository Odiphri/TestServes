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

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE schools MODIFY status ENUM('pending','new','contacted','interested','awaiting_payment','paid','active','renewal_due','suspended','trial','expired','deactivated','archived','lost') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'activated_at')) {
                $table->timestamp('activated_at')->nullable()->after('last_payment_at');
            }

            if (! Schema::hasColumn('schools', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('deactivated_at');
            }

            if (! Schema::hasColumn('schools', 'expired_at')) {
                $table->timestamp('expired_at')->nullable()->after('suspended_at');
            }

            if (! Schema::hasColumn('schools', 'trial_ended_at')) {
                $table->timestamp('trial_ended_at')->nullable()->after('expired_at');
            }

            if (! Schema::hasColumn('schools', 'suspension_reason')) {
                $table->text('suspension_reason')->nullable()->after('deactivation_reason');
            }

            if (! Schema::hasColumn('schools', 'portal_session_version')) {
                $table->unsignedInteger('portal_session_version')->default(1)->after('portal_locked');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('schools')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            foreach (['portal_session_version', 'suspension_reason', 'trial_ended_at', 'expired_at', 'suspended_at', 'activated_at'] as $column) {
                if (Schema::hasColumn('schools', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE schools MODIFY status ENUM('pending','active','suspended','trial','expired','deactivated') NOT NULL DEFAULT 'pending'");
        }
    }
};
