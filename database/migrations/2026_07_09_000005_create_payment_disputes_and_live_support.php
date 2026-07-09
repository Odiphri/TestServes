<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_disputes')) {
            Schema::create('payment_disputes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_record_id')->nullable()->constrained('payment_records')->nullOnDelete();
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->foreignId('school_owner_id')->nullable()->constrained('school_owners')->nullOnDelete();
                $table->foreignId('assigned_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->string('reference')->unique();
                $table->string('subject');
                $table->text('description');
                $table->decimal('disputed_amount', 12, 2)->nullable();
                $table->enum('status', ['open', 'investigating', 'resolved', 'rejected', 'closed'])->default('open');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->text('finance_notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('live_support_conversations')) {
            Schema::create('live_support_conversations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->foreignId('school_owner_id')->nullable()->constrained('school_owners')->nullOnDelete();
                $table->foreignId('assigned_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->string('reference')->unique();
                $table->string('access_token', 80)->unique();
                $table->string('visitor_name')->nullable();
                $table->string('visitor_email')->nullable();
                $table->string('visitor_phone')->nullable();
                $table->string('subject')->nullable();
                $table->enum('status', ['open', 'waiting', 'answered', 'closed'])->default('open');
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('live_support_messages')) {
            Schema::create('live_support_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_support_conversation_id')->constrained('live_support_conversations')->cascadeOnDelete();
                $table->foreignId('platform_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->enum('sender_type', ['visitor', 'admin', 'system'])->default('visitor');
                $table->string('sender_name')->nullable();
                $table->text('message');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('live_support_messages');
        Schema::dropIfExists('live_support_conversations');
        Schema::dropIfExists('payment_disputes');
    }
};
