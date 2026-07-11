<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subscription_plans') || Schema::hasColumn('subscription_plans', 'admin_limit')) {
            return;
        }

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedInteger('admin_limit')->default(1)->after('storage_limit');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('subscription_plans') || ! Schema::hasColumn('subscription_plans', 'admin_limit')) {
            return;
        }

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('admin_limit');
        });
    }
};
