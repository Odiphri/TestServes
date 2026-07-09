<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                if (! Schema::hasColumn('schools', 'address')) {
                    $table->text('address')->nullable()->after('portal_url');
                }

                if (! Schema::hasColumn('schools', 'school_type')) {
                    $table->string('school_type')->nullable()->after('address');
                }

                if (! Schema::hasColumn('schools', 'expected_students_count')) {
                    $table->unsignedInteger('expected_students_count')->nullable()->after('school_type');
                }
            });
        }

        if (Schema::hasTable('school_owners') && ! Schema::hasColumn('school_owners', 'remember_token')) {
            Schema::table('school_owners', function (Blueprint $table) {
                $table->rememberToken()->after('password');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('school_owners') && Schema::hasColumn('school_owners', 'remember_token')) {
            Schema::table('school_owners', function (Blueprint $table) {
                $table->dropRememberToken();
            });
        }

        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                foreach (['expected_students_count', 'school_type', 'address'] as $column) {
                    if (Schema::hasColumn('schools', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
