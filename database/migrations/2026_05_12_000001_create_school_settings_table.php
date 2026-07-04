<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('TestServes');
            $table->string('motto')->nullable();
            $table->text('vision')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('school_address')->nullable();
            $table->string('school_phone')->nullable();
            $table->string('school_email')->nullable();
            $table->integer('exam_duration')->default(120);
            $table->integer('pass_mark')->default(50);
            $table->boolean('auto_grade')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
