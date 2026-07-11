<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('demo_requests')) {
            return;
        }

        DB::statement("ALTER TABLE demo_requests MODIFY status ENUM('new','contacted','scheduled','completed','cancelled','approved') DEFAULT 'new'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('demo_requests')) {
            return;
        }

        DB::table('demo_requests')->where('status', 'approved')->update(['status' => 'completed']);
        DB::statement("ALTER TABLE demo_requests MODIFY status ENUM('new','contacted','scheduled','completed','cancelled') DEFAULT 'new'");
    }
};
