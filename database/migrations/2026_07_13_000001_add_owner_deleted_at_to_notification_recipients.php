<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notification_recipients') || Schema::hasColumn('notification_recipients', 'owner_deleted_at')) {
            return;
        }

        Schema::table('notification_recipients', function (Blueprint $table) {
            $table->timestamp('owner_deleted_at')->nullable()->after('read_at')->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('notification_recipients') || ! Schema::hasColumn('notification_recipients', 'owner_deleted_at')) {
            return;
        }

        Schema::table('notification_recipients', function (Blueprint $table) {
            $table->dropColumn('owner_deleted_at');
        });
    }
};
