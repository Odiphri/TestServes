<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('demo_requests') || Schema::hasColumn('demo_requests', 'subscription_plan_id')) {
            return;
        }

        Schema::table('demo_requests', function (Blueprint $table) {
            $table->foreignId('subscription_plan_id')
                ->nullable()
                ->after('school_id')
                ->constrained('subscription_plans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('demo_requests') || ! Schema::hasColumn('demo_requests', 'subscription_plan_id')) {
            return;
        }

        Schema::table('demo_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subscription_plan_id');
        });
    }
};
