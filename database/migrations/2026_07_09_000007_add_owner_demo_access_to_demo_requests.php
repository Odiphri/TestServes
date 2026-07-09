<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('demo_requests')) {
            return;
        }

        Schema::table('demo_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('demo_requests', 'school_owner_id')) {
                $table->unsignedBigInteger('school_owner_id')->nullable()->after('id')->index();
            }

            if (! Schema::hasColumn('demo_requests', 'school_id')) {
                $table->unsignedBigInteger('school_id')->nullable()->after('school_owner_id')->index();
            }

            if (! Schema::hasColumn('demo_requests', 'demo_token')) {
                $table->string('demo_token')->nullable()->unique()->after('notes');
            }

            if (! Schema::hasColumn('demo_requests', 'demo_access_token')) {
                $table->string('demo_access_token')->nullable()->after('demo_token');
            }

            if (! Schema::hasColumn('demo_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('demo_access_token');
            }

            if (! Schema::hasColumn('demo_requests', 'demo_expires_at')) {
                $table->timestamp('demo_expires_at')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('demo_requests')) {
            return;
        }

        Schema::table('demo_requests', function (Blueprint $table) {
            foreach (['demo_expires_at', 'approved_at', 'demo_access_token', 'demo_token', 'school_id', 'school_owner_id'] as $column) {
                if (Schema::hasColumn('demo_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
