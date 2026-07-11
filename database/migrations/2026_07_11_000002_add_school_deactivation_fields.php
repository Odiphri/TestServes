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

        DB::statement("ALTER TABLE schools MODIFY status ENUM('pending', 'active', 'suspended', 'trial', 'expired', 'deactivated') NOT NULL DEFAULT 'pending'");

        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'deactivation_reason')) {
                $table->text('deactivation_reason')->nullable()->after('subscription_expires_at');
            }

            if (! Schema::hasColumn('schools', 'deactivated_at')) {
                $table->timestamp('deactivated_at')->nullable()->after('deactivation_reason');
            }

            if (! Schema::hasColumn('schools', 'delete_scheduled_at')) {
                $table->timestamp('delete_scheduled_at')->nullable()->after('deactivated_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('schools')) {
            return;
        }

        DB::table('schools')->where('status', 'deactivated')->update(['status' => 'suspended']);

        Schema::table('schools', function (Blueprint $table) {
            foreach (['delete_scheduled_at', 'deactivated_at', 'deactivation_reason'] as $column) {
                if (Schema::hasColumn('schools', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        DB::statement("ALTER TABLE schools MODIFY status ENUM('pending', 'active', 'suspended', 'trial', 'expired') NOT NULL DEFAULT 'pending'");
    }
};
