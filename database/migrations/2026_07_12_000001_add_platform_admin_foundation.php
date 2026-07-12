<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('platform_admins')) {
            $this->expandEnum('platform_admins', 'role', [
                'super_admin',
                'sales_admin',
                'support_admin',
                'finance_admin',
                'operations_admin',
            ], "'super_admin'");
        }

        if (Schema::hasTable('schools')) {
            $this->expandEnum('schools', 'status', [
                'pending',
                'new',
                'contacted',
                'interested',
                'trial',
                'awaiting_payment',
                'active',
                'renewal_due',
                'expired',
                'suspended',
                'deactivated',
                'archived',
                'lost',
            ], "'pending'");
        }

        if (Schema::hasTable('payment_records')) {
            $this->expandEnum('payment_records', 'status', [
                'pending',
                'paid',
                'failed',
                'rejected',
                'refunded',
                'cancelled',
            ], "'pending'");

            Schema::table('payment_records', function (Blueprint $table) {
                if (! Schema::hasColumn('payment_records', 'approved_by_admin_id')) {
                    $table->foreignId('approved_by_admin_id')->nullable()->after('notes')->constrained('platform_admins')->nullOnDelete();
                }
                if (! Schema::hasColumn('payment_records', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by_admin_id');
                }
                if (! Schema::hasColumn('payment_records', 'rejected_by_admin_id')) {
                    $table->foreignId('rejected_by_admin_id')->nullable()->after('approved_at')->constrained('platform_admins')->nullOnDelete();
                }
                if (! Schema::hasColumn('payment_records', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('rejected_by_admin_id');
                }
                if (! Schema::hasColumn('payment_records', 'verified_at')) {
                    $table->timestamp('verified_at')->nullable()->after('rejected_at');
                }
                if (! Schema::hasColumn('payment_records', 'provider_reference')) {
                    $table->string('provider_reference')->nullable()->after('verified_at')->index();
                }
                if (! Schema::hasColumn('payment_records', 'provider_payload')) {
                    $table->json('provider_payload')->nullable()->after('provider_reference');
                }
            });
        }

        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                if (! Schema::hasColumn('activity_logs', 'admin_role')) {
                    $table->string('admin_role')->nullable()->after('platform_admin_id')->index();
                }
                if (! Schema::hasColumn('activity_logs', 'school_id')) {
                    $table->foreignId('school_id')->nullable()->after('target_id')->constrained('schools')->nullOnDelete();
                }
                if (! Schema::hasColumn('activity_logs', 'old_values')) {
                    $table->json('old_values')->nullable()->after('school_id');
                }
                if (! Schema::hasColumn('activity_logs', 'new_values')) {
                    $table->json('new_values')->nullable()->after('old_values');
                }
            });
        }

        if (! Schema::hasTable('school_lifecycle_histories')) {
            Schema::create('school_lifecycle_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->string('previous_status')->nullable();
                $table->string('new_status');
                $table->foreignId('changed_by_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->string('changed_by_role')->nullable();
                $table->text('reason')->nullable();
                $table->timestamps();
                $table->index(['school_id', 'created_at']);
                $table->index(['new_status', 'created_at']);
            });
        }

        if (! Schema::hasTable('notification_campaigns')) {
            Schema::create('notification_campaigns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('created_by_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->string('created_by_role')->nullable();
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->string('type')->default('general')->index();
                $table->string('title');
                $table->text('body');
                $table->string('recipient_scope');
                $table->json('recipient_payload')->nullable();
                $table->string('action_url')->nullable();
                $table->boolean('is_system_notification')->default(false)->index();
                $table->boolean('allows_replies')->default(true);
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->string('status')->default('draft')->index();
                $table->unsignedInteger('recipient_count')->default(0);
                $table->unsignedInteger('successful_deliveries')->default(0);
                $table->unsignedInteger('failed_deliveries')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('notification_recipients')) {
            Schema::create('notification_recipients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('notification_campaign_id')->constrained('notification_campaigns')->cascadeOnDelete();
                $table->morphs('notifiable');
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->text('failure_reason')->nullable();
                $table->timestamps();
                $table->unique(['notification_campaign_id', 'notifiable_type', 'notifiable_id'], 'notification_recipient_unique');
                $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'notification_recipient_read_idx');
            });
        }

        if (! Schema::hasTable('notification_threads')) {
            Schema::create('notification_threads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('notification_recipient_id')->unique()->constrained('notification_recipients')->cascadeOnDelete();
                $table->string('status')->default('open')->index();
                $table->foreignId('closed_by_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('notification_messages')) {
            Schema::create('notification_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('notification_thread_id')->constrained('notification_threads')->cascadeOnDelete();
                $table->morphs('sender');
                $table->text('message');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->index(['notification_thread_id', 'created_at'], 'notification_messages_thread_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_messages');
        Schema::dropIfExists('notification_threads');
        Schema::dropIfExists('notification_recipients');
        Schema::dropIfExists('notification_campaigns');
        Schema::dropIfExists('school_lifecycle_histories');
    }

    private function expandEnum(string $table, string $column, array $values, string $default): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $quoted = collect($values)->map(fn (string $value) => "'{$value}'")->implode(',');
        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` ENUM({$quoted}) NOT NULL DEFAULT {$default}");
    }
};
