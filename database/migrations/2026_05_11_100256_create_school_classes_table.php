<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('level', ['JSS1', 'JSS2', 'JSS3', 'SS1', 'SS2', 'SS3']);
            $table->enum('stream', ['Science', 'Art', 'Commercial', 'General'])->nullable();
            $table->text('description')->nullable();
            $table->foreignId('class_teacher_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
