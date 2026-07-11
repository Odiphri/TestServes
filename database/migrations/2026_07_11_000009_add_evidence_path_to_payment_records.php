<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_records') || Schema::hasColumn('payment_records', 'evidence_path')) {
            return;
        }

        Schema::table('payment_records', function (Blueprint $table) {
            $table->string('evidence_path')->nullable()->after('receipt_number');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('payment_records') || ! Schema::hasColumn('payment_records', 'evidence_path')) {
            return;
        }

        Schema::table('payment_records', function (Blueprint $table) {
            $table->dropColumn('evidence_path');
        });
    }
};
