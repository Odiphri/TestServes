<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User Management
            'users.create',
            'users.edit',
            'users.delete',
            'users.view',
            'users.assign-roles',
            
            // Academic Management
            'classes.create',
            'classes.edit',
            'classes.delete',
            'classes.view',
            'subjects.create',
            'subjects.edit',
            'subjects.delete',
            'subjects.view',
            
            // Exam Management
            'exams.create',
            'exams.edit',
            'exams.delete',
            'exams.view',
            'exams.live-toggle',
            'exams.results-toggle',
            'exams.ai-generate',
            'exams.reset-attempts',
            'exams.monitor',
            
            // Question Management
            'questions.create',
            'questions.edit',
            'questions.delete',
            'questions.view',
            
            // Exam Taking
            'exams.take',
            'exams.view-results',
            
            // Financial Management
            'payments.create',
            'payments.edit',
            'payments.view',
            'payments.reports',
            'bursary.manage',
            'overrides.create',
            'overrides.view',
            'exams.override_access',
            'exams.edit_all',
            'results.view_all',
            'students.manage',
            'exams.allow_retakes',
            'student_roles.manage',
            
            // Attendance Management
            'attendance.mark',
            'attendance.view',
            
            // Change Requests
            'change-requests.create',
            'change-requests.approve',
            'change-requests.view',
            
            // Profile Management
            'profile.edit',
            'profile.view',
            
            // System Settings
            'system.settings',
            'system.reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $roles = [
            'admin' => [
                'users.create', 'users.edit', 'users.delete', 'users.view', 'users.assign-roles',
                'classes.create', 'classes.edit', 'classes.delete', 'classes.view',
                'subjects.create', 'subjects.edit', 'subjects.delete', 'subjects.view',
                'exams.create', 'exams.edit', 'exams.delete', 'exams.view', 'exams.live-toggle', 'exams.results-toggle', 'exams.ai-generate', 'exams.reset-attempts', 'exams.monitor',
                'questions.create', 'questions.edit', 'questions.delete', 'questions.view',
                'payments.create', 'payments.edit', 'payments.view', 'payments.reports', 'overrides.create', 'overrides.view',
                'attendance.mark', 'attendance.view',
                'change-requests.approve', 'change-requests.view',
                'profile.edit', 'profile.view',
                'system.settings', 'system.reports',
            ],
            
            'hod' => [
                'classes.view', 'classes.edit',
                'subjects.view', 'subjects.edit',
                'exams.create', 'exams.edit', 'exams.delete', 'exams.view', 'exams.live-toggle', 'exams.results-toggle', 'exams.ai-generate', 'exams.reset-attempts', 'exams.monitor',
                'questions.create', 'questions.edit', 'questions.delete', 'questions.view',
                'overrides.create', 'overrides.view',
                'change-requests.approve', 'change-requests.view',
                'profile.edit', 'profile.view',
            ],
            
            'cbt_personnel' => [
                'exams.create', 'exams.edit', 'exams.delete', 'exams.view', 'exams.live-toggle', 'exams.results-toggle', 'exams.ai-generate', 'exams.reset-attempts', 'exams.monitor',
                'questions.create', 'questions.edit', 'questions.delete', 'questions.view',
                'profile.edit', 'profile.view',
            ],
            
            'teacher' => [
                'classes.view',
                'subjects.view',
                'exams.create', 'exams.edit', 'exams.delete', 'exams.view', 'exams.live-toggle', 'exams.results-toggle', 'exams.ai-generate', 'exams.reset-attempts',
                'questions.create', 'questions.edit', 'questions.delete', 'questions.view',
                'attendance.mark', 'attendance.view',
                'profile.edit', 'profile.view',
            ],
            
            'prefect' => [
                'users.view',
                'users.edit',
                'profile.edit', 'profile.view',
            ],
            
            'student' => [
                'exams.take',
                'exams.view-results',
                'change-requests.create',
                'change-requests.view',
                'profile.edit', 'profile.view',
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($permissions);
        }
    }
}
