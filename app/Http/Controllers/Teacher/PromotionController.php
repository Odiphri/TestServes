<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\StudentPromotionService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request, StudentPromotionService $promotionService)
    {
        $classIds = $this->assignedClassIds($request);

        $students = User::query()
            ->whereIn('role', ['student', 'prefect'])
            ->whereIn('school_class_id', $classIds)
            ->with('assignedClass')
            ->orderBy('school_class_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('teacher.promotions.index', [
            'students' => $students,
            'assignedClasses' => SchoolClass::whereIn('id', $classIds)->orderBy('level')->orderBy('stream')->get(),
            'promotionService' => $promotionService,
        ]);
    }

    public function promote(Request $request, User $student, StudentPromotionService $promotionService)
    {
        $this->ensureStudentInTeacherClass($request, $student);

        $targetClass = $promotionService->promoteStudent($student);

        return back()->with(
            $targetClass ? 'success' : 'error',
            $targetClass
                ? "{$student->full_name} moved to {$targetClass->full_name}."
                : "{$student->full_name} cannot be promoted beyond the available class hierarchy."
        );
    }

    public function demote(Request $request, User $student, StudentPromotionService $promotionService)
    {
        $this->ensureStudentInTeacherClass($request, $student);

        $targetClass = $promotionService->demoteStudent($student);

        return back()->with(
            $targetClass ? 'success' : 'error',
            $targetClass
                ? "{$student->full_name} moved to {$targetClass->full_name}."
                : "{$student->full_name} cannot be demoted below the available class hierarchy."
        );
    }

    private function ensureStudentInTeacherClass(Request $request, User $student): void
    {
        abort_unless(in_array($student->role, ['student', 'prefect'], true), 404);

        abort_unless(
            in_array((int) $student->school_class_id, $this->assignedClassIds($request), true),
            403
        );
    }

    /**
     * @return array<int>
     */
    private function assignedClassIds(Request $request): array
    {
        $user = $request->user();

        return SchoolClass::query()
            ->where(function ($query) use ($user) {
                $query->where('class_teacher_id', $user->id)
                    ->orWhereHas('teachers', fn ($teacherQuery) => $teacherQuery->whereKey($user->id))
                    ->orWhereHas('assignedStaff', fn ($staffQuery) => $staffQuery->whereKey($user->id));
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
