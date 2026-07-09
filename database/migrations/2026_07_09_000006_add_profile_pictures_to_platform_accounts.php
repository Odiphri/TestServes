<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('school_owners') && ! Schema::hasColumn('school_owners', 'profile_picture')) {
            Schema::table('school_owners', function (Blueprint $table) {
                $table->string('profile_picture')->nullable()->after('phone');
            });
        }

        if (Schema::hasTable('platform_admins') && ! Schema::hasColumn('platform_admins', 'profile_picture')) {
            Schema::table('platform_admins', function (Blueprint $table) {
                $table->string('profile_picture')->nullable()->after('phone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('school_owners') && Schema::hasColumn('school_owners', 'profile_picture')) {
            Schema::table('school_owners', function (Blueprint $table) {
                $table->dropColumn('profile_picture');
            });
        }

        if (Schema::hasTable('platform_admins') && Schema::hasColumn('platform_admins', 'profile_picture')) {
            Schema::table('platform_admins', function (Blueprint $table) {
                $table->dropColumn('profile_picture');
            });
        }
    }
};
