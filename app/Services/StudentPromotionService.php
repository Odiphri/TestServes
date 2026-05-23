<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\User;

class StudentPromotionService
{
    private array $nextLevels = [
        'JSS1' => 'JSS2',
        'JSS2' => 'JSS3',
        'JSS3' => 'SS1',
        'SS1' => 'SS2',
        'SS2' => 'SS3',
    ];

    private array $previousLevels = [
        'JSS2' => 'JSS1',
        'JSS3' => 'JSS2',
        'SS1' => 'JSS3',
        'SS2' => 'SS1',
        'SS3' => 'SS2',
    ];

    public function promoteAllStudents(): int
    {
        $promoted = 0;

        User::query()
            ->whereIn('role', ['student', 'prefect'])
            ->whereNotNull('school_class_id')
            ->with('assignedClass')
            ->orderBy('id')
            ->chunkById(100, function ($students) use (&$promoted) {
                foreach ($students as $student) {
                    $nextClass = $this->nextClass($student->assignedClass);

                    if ($nextClass && (int) $nextClass->id !== (int) $student->school_class_id) {
                        $student->forceFill(['school_class_id' => $nextClass->id])->save();
                        $promoted++;
                    }
                }
            });

        return $promoted;
    }

    public function promoteStudent(User $student): ?SchoolClass
    {
        return $this->moveStudent($student, 'next');
    }

    public function demoteStudent(User $student): ?SchoolClass
    {
        return $this->moveStudent($student, 'previous');
    }

    public function nextClass(?SchoolClass $class): ?SchoolClass
    {
        if (!$class || !isset($this->nextLevels[$class->level])) {
            return null;
        }

        return $this->matchingClass($this->nextLevels[$class->level], $class->stream);
    }

    public function previousClass(?SchoolClass $class): ?SchoolClass
    {
        if (!$class || !isset($this->previousLevels[$class->level])) {
            return null;
        }

        return $this->matchingClass($this->previousLevels[$class->level], $class->stream);
    }

    private function moveStudent(User $student, string $direction): ?SchoolClass
    {
        $student->loadMissing('assignedClass');

        $targetClass = $direction === 'next'
            ? $this->nextClass($student->assignedClass)
            : $this->previousClass($student->assignedClass);

        if (!$targetClass) {
            return null;
        }

        $student->update(['school_class_id' => $targetClass->id]);

        return $targetClass;
    }

    private function matchingClass(string $level, ?string $stream): ?SchoolClass
    {
        $query = SchoolClass::query()->active()->where('level', $level);

        $sameStreamClass = $stream
            ? (clone $query)->where('stream', $stream)->orderBy('name')->first()
            : null;

        return $sameStreamClass ?: $query->orderBy('stream')->orderBy('name')->first();
    }
}
