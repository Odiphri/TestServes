<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('school_settings')) {
            return;
        }

        Schema::table('school_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('school_settings', 'primary_color')) {
                $table->string('primary_color', 7)->default('#0B1F5B')->after('logo_path');
            }

            if (! Schema::hasColumn('school_settings', 'secondary_color')) {
                $table->string('secondary_color', 7)->default('#081645')->after('primary_color');
            }

            if (! Schema::hasColumn('school_settings', 'accent_color')) {
                $table->string('accent_color', 7)->default('#1E88FF')->after('secondary_color');
            }

            if (! Schema::hasColumn('school_settings', 'enabled_features')) {
                $table->json('enabled_features')->nullable()->after('accent_color');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('school_settings')) {
            return;
        }

        Schema::table('school_settings', function (Blueprint $table) {
            foreach (['enabled_features', 'accent_color', 'secondary_color', 'primary_color'] as $column) {
                if (Schema::hasColumn('school_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
