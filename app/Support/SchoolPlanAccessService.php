<?php

namespace App\Support;

use App\Models\School;
use Illuminate\Support\Str;

class SchoolPlanAccessService
{
    public function featureForRoute(?string $routeName): ?string
    {
        if (blank($routeName)) {
            return null;
        }

        $map = [
            'admin.dashboard' => 'Admin dashboard',
            'teacher.dashboard' => 'Teacher dashboard',
            'student.dashboard' => 'Student dashboard',
            'prefect.dashboard' => 'Prefect dashboard',
            'hod.dashboard' => 'HOD dashboard',
            'cbt.dashboard' => 'CBT personnel dashboard',
            '*.students*' => 'Student and staff management',
            '*.staff*' => 'Student and staff management',
            '*.users*' => 'Student and staff management',
            '*.classes*' => 'Class management',
            '*.subjects*' => 'Subject management',
            'student.exams*' => 'Student exam taking',
            'prefect.exams*' => 'Student exam taking',
            '*.exams*' => 'Exam creation',
            '*.questions*' => 'Question bank',
            '*.ai-questions*' => 'AI question generation',
            '*.monitor*' => 'Live exam monitoring',
            '*.results*' => 'Result calculation',
            '*.overrides*' => 'HOD override approvals',
            '*.payments*' => 'Bursary and fee tracking',
            '*.attendance*' => 'Attendance management',
            '*.promotions*' => 'Academic session promotion',
            '*.profile*' => 'Student profile management',
            '*.directory*' => 'Student directory',
            'traffic.*' => 'Traffic analytics',
            'academic-sessions.*' => 'Academic session promotion',
            'student-roles.*' => 'Student profile management',
            'prefect-roles.*' => 'Prefect dashboard',
            'hod.*' => 'HOD dashboard',
            'cbt.*' => 'CBT personnel dashboard',
            'teacher.*' => 'Teacher dashboard',
            'student.*' => 'Student dashboard',
            'prefect.*' => 'Prefect dashboard',
        ];

        foreach ($map as $pattern => $feature) {
            if (Str::is($pattern, $routeName)) {
                return $feature;
            }
        }

        return null;
    }

    public function allows(?School $school, ?string $feature): bool
    {
        if (! $school || blank($feature)) {
            return true;
        }

        $features = $school->plan?->features ?? [];

        return in_array($feature, $features, true);
    }
}
