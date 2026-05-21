<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'view.bursary',
            'manage.users',
            'manage.classes',
            'manage.exams',
            'system.reports',
            'bursary.manage',
            'exams.edit_all',
            'results.view_all',
            'students.manage',
            'exams.allow_retakes',
            'student_roles.manage',
            'attendance.mark',
            'exams.override_access',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
