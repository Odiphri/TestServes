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
            if (! Schema::hasColumn('schools', 'tenant_connection')) {
                $table->string('tenant_connection')->nullable()->after('portal_url');
            }

            if (! Schema::hasColumn('schools', 'tenant_database')) {
                $table->string('tenant_database')->nullable()->after('tenant_connection');
            }

            if (! Schema::hasColumn('schools', 'tenant_database_created_at')) {
                $table->timestamp('tenant_database_created_at')->nullable()->after('tenant_database');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('schools')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            foreach (['tenant_database_created_at', 'tenant_database', 'tenant_connection'] as $column) {
                if (Schema::hasColumn('schools', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
