<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('legal_documents')) {
            Schema::create('legal_documents', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->index();
                $table->string('version')->default('1.0');
                $table->longText('content');
                $table->timestamp('effective_at')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->boolean('is_published')->default(false);
                $table->foreignId('created_by_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->foreignId('updated_by_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->timestamps();
                $table->unique(['slug', 'version']);
            });
        }

        if (! Schema::hasTable('legal_acceptances')) {
            Schema::create('legal_acceptances', function (Blueprint $table) {
                $table->id();
                $table->morphs('acceptor');
                $table->string('privacy_policy_version');
                $table->string('terms_version');
                $table->timestamp('accepted_at');
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->string('source')->default('owner_registration');
                $table->timestamps();
                $table->unique(['acceptor_type', 'acceptor_id', 'source'], 'legal_acceptances_unique_source');
            });
        }

        if (! Schema::hasTable('contact_inquiries')) {
            Schema::create('contact_inquiries', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email');
                $table->string('phone')->nullable();
                $table->string('school_name')->nullable();
                $table->string('category', 80)->index();
                $table->string('subject');
                $table->text('message');
                $table->enum('status', ['new', 'assigned', 'in_progress', 'responded', 'closed', 'spam'])->default('new')->index();
                $table->foreignId('assigned_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->string('source')->default('public_contact');
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('submitted_at')->nullable()->index();
                $table->timestamp('responded_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();
                $table->index(['email', 'submitted_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_inquiries');
        Schema::dropIfExists('legal_acceptances');
        Schema::dropIfExists('legal_documents');
    }
};
