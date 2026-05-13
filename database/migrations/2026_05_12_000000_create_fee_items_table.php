<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('fee_type', ['compulsory', 'optional'])->default('compulsory');
            $table->boolean('applies_to_all_classes')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fee_item_school_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['fee_item_id', 'school_class_id']);
        });

        Schema::create('student_fee_exemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('fee_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('removed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'fee_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fee_exemptions');
        Schema::dropIfExists('fee_item_school_class');
        Schema::dropIfExists('fee_items');
    }
};
