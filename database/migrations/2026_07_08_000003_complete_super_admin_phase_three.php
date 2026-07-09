<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('platform_admins') && ! Schema::hasColumn('platform_admins', 'deleted_at')) {
            Schema::table('platform_admins', fn (Blueprint $table) => $table->softDeletes());
        }

        if (Schema::hasTable('school_owners')) {
            Schema::table('school_owners', function (Blueprint $table) {
                if (! Schema::hasColumn('school_owners', 'status')) {
                    $table->enum('status', ['active', 'disabled', 'pending'])->default('active')->after('is_primary');
                }
                if (! Schema::hasColumn('school_owners', 'last_login_at')) {
                    $table->timestamp('last_login_at')->nullable()->after('status');
                }
            });
        }

        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                if (! Schema::hasColumn('schools', 'subscription_status')) {
                    $table->enum('subscription_status', ['pending', 'active', 'trial', 'expired', 'cancelled'])->default('pending')->after('status');
                }
            });
        }

        if (Schema::hasTable('subscription_plans')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                if (! Schema::hasColumn('subscription_plans', 'slug')) {
                    $table->string('slug')->nullable()->unique()->after('name');
                }
                if (! Schema::hasColumn('subscription_plans', 'storage_limit')) {
                    $table->unsignedInteger('storage_limit')->nullable()->after('exam_limit');
                }
                if (! Schema::hasColumn('subscription_plans', 'is_recommended')) {
                    $table->boolean('is_recommended')->default(false)->after('status');
                }
                if (! Schema::hasColumn('subscription_plans', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (! Schema::hasTable('payment_records')) {
            Schema::create('payment_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->foreignId('school_owner_id')->nullable()->constrained('school_owners')->nullOnDelete();
                $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('currency', 3)->default('NGN');
                $table->enum('payment_method', ['paystack', 'bank_transfer', 'cash', 'manual'])->default('manual');
                $table->string('payment_reference')->nullable()->index();
                $table->enum('status', ['pending', 'paid', 'failed', 'rejected', 'refunded'])->default('pending');
                $table->date('payment_date')->nullable();
                $table->date('period_start')->nullable();
                $table->date('period_end')->nullable();
                $table->string('receipt_number')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('demo_requests')) {
            Schema::create('demo_requests', function (Blueprint $table) {
                $table->id();
                $table->string('school_name');
                $table->string('contact_person')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('location')->nullable();
                $table->text('message')->nullable();
                $table->dateTime('preferred_demo_date')->nullable();
                $table->enum('status', ['new', 'contacted', 'scheduled', 'completed', 'cancelled'])->default('new');
                $table->foreignId('assigned_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->foreignId('school_owner_id')->nullable()->constrained('school_owners')->nullOnDelete();
                $table->string('subject');
                $table->text('message');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
                $table->foreignId('assigned_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->text('internal_notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('platform_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->string('action')->index();
                $table->text('description');
                $table->string('target_type')->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
                $table->index(['target_type', 'target_id']);
            });
        }

        if (! Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('demo_requests');
        Schema::dropIfExists('payment_records');
    }
};
