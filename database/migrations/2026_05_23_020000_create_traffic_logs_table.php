<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traffic_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('role')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('pages_visited')->nullable();
            $table->timestamp('login_at')->index();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamp('logout_at')->nullable()->index();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traffic_logs');
    }
};
