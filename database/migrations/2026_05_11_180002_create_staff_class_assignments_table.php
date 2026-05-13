<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_class_assignments', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['user_id', 'school_class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_class_assignments');
    }
};
