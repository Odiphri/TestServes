<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->json('target_class_ids')->nullable()->after('school_class_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE school_classes MODIFY stream ENUM('A', 'B', 'C', 'Science', 'Art', 'Commercial', 'General') NULL");
        }

        foreach (['A', 'B', 'C'] as $arm) {
            DB::table('school_classes')
                ->where('level', 'like', 'JSS%')
                ->where('name', 'like', '%' . $arm)
                ->update(['stream' => $arm]);
        }

        DB::table('school_classes')
            ->where('level', 'like', 'JSS%')
            ->where('stream', 'General')
            ->update(['stream' => 'A']);
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('target_class_ids');
        });

        DB::table('school_classes')
            ->where('level', 'like', 'JSS%')
            ->whereIn('stream', ['A', 'B', 'C'])
            ->update(['stream' => 'General']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE school_classes MODIFY stream ENUM('Science', 'Art', 'Commercial', 'General') NULL");
        }
    }
};
