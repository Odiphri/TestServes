<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('platform_admins')) {
            Schema::create('platform_admins', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('password');
                $table->enum('role', ['super_admin', 'sales_admin', 'support_admin', 'finance_admin'])->default('super_admin');
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('subscription_plans')) {
            Schema::create('subscription_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('monthly_price', 12, 2)->default(0);
                $table->decimal('yearly_price', 12, 2)->default(0);
                $table->unsignedInteger('student_limit')->nullable();
                $table->unsignedInteger('staff_limit')->nullable();
                $table->unsignedInteger('exam_limit')->nullable();
                $table->unsignedInteger('trial_days')->default(0);
                $table->json('features')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('schools')) {
            Schema::create('schools', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('portal_url')->nullable();
                $table->enum('status', ['pending', 'active', 'suspended', 'trial', 'expired'])->default('pending');
                $table->date('subscription_starts_at')->nullable();
                $table->date('subscription_expires_at')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('school_owners')) {
            Schema::create('school_owners', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('password')->nullable();
                $table->boolean('is_primary')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('school_branding_settings')) {
            Schema::create('school_branding_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->unique()->constrained('schools')->cascadeOnDelete();
                $table->string('primary_color', 7)->default('#2563eb');
                $table->string('secondary_color', 7)->default('#0f172a');
                $table->string('accent_color', 7)->default('#22c55e');
                $table->string('logo_path')->nullable();
                $table->string('short_name')->nullable();
                $table->string('portal_display_name')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('school_subscriptions')) {
            Schema::create('school_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
                $table->date('starts_at')->nullable();
                $table->date('expires_at')->nullable();
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->string('billing_cycle')->nullable();
                $table->enum('status', ['pending', 'active', 'trial', 'expired', 'cancelled'])->default('pending');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_subscriptions');
        Schema::dropIfExists('school_branding_settings');
        Schema::dropIfExists('school_owners');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('platform_admins');
    }
};
