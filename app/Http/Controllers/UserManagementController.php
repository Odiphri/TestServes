<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\PrefectRole;
use App\Models\SchoolClass;
use App\Models\StudentRole;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    private array $staffPermissions = [
        'exams.edit_all' => ['View & Edit All Exams', 'Access and modify all exams across all staff members'],
        'results.view_all' => ['View All Results', 'See student results for any exam school-wide'],
        'students.manage' => ['Manage Students', 'Add, edit and delete student accounts'],
        'exams.allow_retakes' => ['Allow Retakes', "Delete a student's exam result so they can retake it"],
        'student_roles.manage' => ['Manage Student Roles', 'Add new roles, edit role names, and assign roles to students'],
        'bursary.manage' => ['Manage Bursary', 'Create fee types, record payments and manage the fee structure'],
        'attendance.mark' => ['Mark Attendance', 'Record daily attendance for their assigned class'],
        'exams.override_access' => ['Override Exam Access', 'Grant exam access to students with outstanding fees'],
    ];

    public function students(Request $request)
    {
        $this->ensureStudentManager($request);
        $manager = $request->user();
        $managedClassIds = $this->managedStudentClassIds($manager);
        $canManageAllClasses = $this->canManageAllStudentClasses($manager);

        $studentsQuery = User::with(['profile', 'assignedClass', 'subjects', 'studentRole', 'prefectRole'])
            ->whereIn('role', ['student', 'prefect'])
            ->when(! $canManageAllClasses, fn ($query) => $query->whereIn('school_class_id', $managedClassIds));

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $this->applyNameSearch($studentsQuery, $search);
        }

        if ($request->filled('class_id')) {
            $studentsQuery->where('school_class_id', $request->integer('class_id'));
        }

        $students = $studentsQuery->latest()->paginate(20)->withQueryString();
        $classes = $canManageAllClasses
            ? SchoolClass::active()->orderBy('level')->orderBy('name')->get()
            : SchoolClass::active()
                ->whereIn('id', $managedClassIds)
                ->orderBy('level')
                ->orderBy('name')
                ->get();

        return view('management.students.index', [
            'students' => $students,
            'classes' => $classes,
            'subjects' => Subject::active()
                ->with('schoolClass')
                ->when(! $canManageAllClasses, fn ($query) => $query->whereIn('school_class_id', $managedClassIds))
                ->orderBy('name')
                ->get(),
            'studentRoles' => StudentRole::active()->orderBy('name')->get(),
            'prefectRoles' => PrefectRole::active()->orderBy('name')->get(),
            'routePrefix' => $this->routePrefix($request),
            'canEdit' => true,
            'search' => $request->query('search'),
            'selectedClassId' => $request->query('class_id'),
        ]);
    }

    public function storeStudent(Request $request)
    {
        $this->ensureStudentManager($request);
        $this->defaultTeacherStudentClass($request);

        $validated = $request->validate([
            'portal_id' => ['required', 'string', 'max:255', 'unique:users,portal_id'],
            'name' => ['required', 'string', 'max:255'],
            'account_type' => ['required', Rule::in(['student', 'prefect'])],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'student_role_id' => ['nullable', 'required_if:account_type,student', 'exists:student_roles,id'],
            'prefect_role_id' => ['nullable', 'required_if:account_type,prefect', 'exists:prefect_roles,id'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'sex' => ['required', Rule::in(['male', 'female'])],
            'complexion' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:4'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        $this->ensureCanManageStudentClass($request, (int) $validated['school_class_id']);
        $this->ensureSubjectsBelongToClass($validated['subject_ids'] ?? [], (int) $validated['school_class_id']);

        [$firstName, $lastName] = $this->splitName($validated['name']);

        DB::transaction(function () use ($request, $validated, $firstName, $lastName) {
            $roleName = $validated['account_type'];
            $prefectRole = $roleName === 'prefect'
                ? PrefectRole::find($validated['prefect_role_id'])
                : null;

            $student = User::create([
                'portal_id' => $validated['portal_id'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'password' => Hash::make($validated['password']),
                'role' => $roleName,
                'student_role_id' => $roleName === 'student' ? $validated['student_role_id'] : null,
                'prefect_role_id' => $roleName === 'prefect' ? $validated['prefect_role_id'] : null,
                'prefect_title' => $prefectRole?->name,
                'school_class_id' => $validated['school_class_id'],
                'must_change_password' => false,
                'is_active' => true,
            ]);

            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $student->assignRole($roleName);
            $student->subjects()->sync($validated['subject_ids'] ?? []);
            $this->createProfile($request, $student, [
                'age' => $validated['age'],
                'gender' => $validated['sex'],
                'complexion' => $validated['complexion'],
            ]);
        });

        return back()->with('success', 'Student account created successfully.');
    }

    public function updateStudent(Request $request, User $student)
    {
        $this->ensureStudentManager($request);
        $this->ensureStudentMember($student);
        $this->ensureCanManageStudentClass($request, (int) $student->school_class_id);
        $this->defaultTeacherStudentClass($request);

        $validated = $request->validate([
            'portal_id' => ['required', 'string', 'max:255', 'unique:users,portal_id,' . $student->id],
            'name' => ['required', 'string', 'max:255'],
            'account_type' => ['required', Rule::in(['student', 'prefect'])],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'student_role_id' => ['nullable', 'required_if:account_type,student', 'exists:student_roles,id'],
            'prefect_role_id' => ['nullable', 'required_if:account_type,prefect', 'exists:prefect_roles,id'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'sex' => ['required', Rule::in(['male', 'female'])],
            'complexion' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:4'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->ensureCanManageStudentClass($request, (int) $validated['school_class_id']);
        $this->ensureSubjectsBelongToClass($validated['subject_ids'] ?? [], (int) $validated['school_class_id']);

        [$firstName, $lastName] = $this->splitName($validated['name']);

        DB::transaction(function () use ($request, $student, $validated, $firstName, $lastName) {
            $roleName = $validated['account_type'];
            $prefectRole = $roleName === 'prefect'
                ? PrefectRole::find($validated['prefect_role_id'])
                : null;

            $payload = [
                'portal_id' => $validated['portal_id'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role' => $roleName,
                'student_role_id' => $roleName === 'student' ? $validated['student_role_id'] : null,
                'prefect_role_id' => $roleName === 'prefect' ? $validated['prefect_role_id'] : null,
                'prefect_title' => $prefectRole?->name,
                'school_class_id' => $validated['school_class_id'],
                'is_active' => $request->boolean('is_active'),
            ];

            if (!empty($validated['password'])) {
                $payload['password'] = Hash::make($validated['password']);
                $payload['must_change_password'] = false;
            }

            $student->update($payload);

            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $student->syncRoles([$roleName]);
            $student->subjects()->sync($validated['subject_ids'] ?? []);

            $profile = $student->profile ?: $student->profile()->create();
            $profile->update([
                'age' => $validated['age'],
                'gender' => $validated['sex'],
                'complexion' => $validated['complexion'],
            ]);

            if ($request->hasFile('profile_picture')) {
                $profile->updateProfilePicture($request->file('profile_picture'));
            }
        });

        return back()->with('success', 'Student updated successfully.');
    }

    public function destroyStudent(Request $request, User $student)
    {
        $this->ensureStudentManager($request);
        $this->ensureStudentMember($student);
        $this->ensureCanManageStudentClass($request, (int) $student->school_class_id);

        $student->delete();

        return back()->with('success', 'Student deleted successfully.');
    }

    public function staff(Request $request)
    {
        $this->ensureManager($request, ['admin', 'hod']);

        $staffQuery = User::with(['profile', 'teachingSubjects.schoolClass', 'assignedClasses'])
            ->whereIn('role', ['teacher', 'hod', 'cbt_personnel']);

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $this->applyNameSearch($staffQuery, $search);
        }

        if ($request->filled('role')) {
            $staffQuery->where('role', $request->query('role'));
        }

        $staff = $staffQuery->latest()->paginate(20)->withQueryString();

        $jssSubjects = Subject::active()
            ->with('schoolClass')
            ->whereHas('schoolClass', fn ($query) => $query->whereIn('level', ['JSS1', 'JSS2', 'JSS3']))
            ->orderBy('name')
            ->get();

        $sssSubjects = Subject::active()
            ->with('schoolClass')
            ->whereHas('schoolClass', fn ($query) => $query->whereIn('level', ['SS1', 'SS2', 'SS3']))
            ->orderBy('name')
            ->get();

        return view('management.staff.index', [
            'staff' => $staff,
            'jssSubjects' => $jssSubjects,
            'sssSubjects' => $sssSubjects,
            'classes' => SchoolClass::active()->orderBy('level')->orderBy('stream')->get(),
            'staffPermissions' => $this->staffPermissions,
            'routePrefix' => $this->routePrefix($request),
            'search' => $request->query('search'),
            'selectedRole' => $request->query('role'),
        ]);
    }

    public function storeStaff(Request $request)
    {
        $this->ensureManager($request, ['admin', 'hod']);

        $validated = $request->validate([
            'portal_id' => ['required', 'string', 'max:255', 'unique:users,portal_id'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(['teacher', 'hod', 'cbt_personnel'])],
            'password' => ['required', 'string', 'min:4'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
            'jss_subject_ids' => ['nullable', 'array'],
            'jss_subject_ids.*' => ['exists:subjects,id'],
            'sss_subject_ids' => ['nullable', 'array'],
            'sss_subject_ids.*' => ['exists:subjects,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::in(array_keys($this->staffPermissions))],
        ]);

        [$firstName, $lastName] = $this->splitName($validated['name']);
        $subjectIds = collect($validated['jss_subject_ids'] ?? [])
            ->merge($validated['sss_subject_ids'] ?? [])
            ->unique()
            ->values();

        DB::transaction(function () use ($request, $validated, $firstName, $lastName, $subjectIds) {
            $staff = User::create([
                'portal_id' => $validated['portal_id'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'must_change_password' => false,
                'is_active' => true,
            ]);

            Role::firstOrCreate(['name' => $validated['role'], 'guard_name' => 'web']);
            $staff->assignRole($validated['role']);
            $this->syncTeachingSubjects($staff, $subjectIds->all());
            $this->syncDirectPermissions($staff, $validated['permissions'] ?? []);
            $this->createProfile($request, $staff);
        });

        return back()->with('success', 'Staff member created successfully.');
    }

    public function updateStaff(Request $request, User $staff)
    {
        $this->ensureManager($request, ['admin', 'hod']);
        $this->ensureStaffMember($staff);

        $validated = $request->validate([
            'portal_id' => ['required', 'string', 'max:255', 'unique:users,portal_id,' . $staff->id],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(['teacher', 'hod', 'cbt_personnel'])],
            'password' => ['nullable', 'string', 'min:4'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
            'jss_subject_ids' => ['nullable', 'array'],
            'jss_subject_ids.*' => ['exists:subjects,id'],
            'sss_subject_ids' => ['nullable', 'array'],
            'sss_subject_ids.*' => ['exists:subjects,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::in(array_keys($this->staffPermissions))],
            'is_active' => ['nullable', 'boolean'],
        ]);

        [$firstName, $lastName] = $this->splitName($validated['name']);
        $subjectIds = collect($validated['jss_subject_ids'] ?? [])
            ->merge($validated['sss_subject_ids'] ?? [])
            ->unique()
            ->values();

        DB::transaction(function () use ($request, $staff, $validated, $firstName, $lastName, $subjectIds) {
            $payload = [
                'portal_id' => $validated['portal_id'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role' => $validated['role'],
                'is_active' => $request->boolean('is_active'),
            ];

            if (!empty($validated['password'])) {
                $payload['password'] = Hash::make($validated['password']);
                $payload['must_change_password'] = false;
            }

            $staff->update($payload);

            Role::firstOrCreate(['name' => $validated['role'], 'guard_name' => 'web']);
            $staff->syncRoles([$validated['role']]);
            $this->syncTeachingSubjects($staff, $subjectIds->all());
            $this->syncDirectPermissions($staff, $validated['permissions'] ?? []);

            if ($request->hasFile('profile_picture')) {
                $profile = $staff->profile ?: $staff->profile()->create();
                $profile->updateProfilePicture($request->file('profile_picture'));
            }
        });

        return back()->with('success', 'Staff member updated successfully.');
    }

    public function destroyStaff(Request $request, User $staff)
    {
        $this->ensureManager($request, ['admin', 'hod']);
        $this->ensureStaffMember($staff);

        abort_if($request->user()->is($staff), 422, 'You cannot delete your own account.');

        $staff->delete();

        return back()->with('success', 'Staff member deleted successfully.');
    }

    public function assignClass(Request $request, User $staff)
    {
        $this->ensureManager($request, ['admin', 'hod']);
        $this->ensureStaffMember($staff);

        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
        ]);

        $staff->assignedClasses()->syncWithoutDetaching([$validated['school_class_id']]);

        if ($staff->role === 'teacher') {
            SchoolClass::where('id', $validated['school_class_id'])
                ->whereNull('class_teacher_id')
                ->update(['class_teacher_id' => $staff->id]);
        }

        return back()->with('success', 'Class assigned successfully.');
    }

    public function unassignClass(Request $request, User $staff, SchoolClass $class)
    {
        $this->ensureManager($request, ['admin', 'hod']);
        $this->ensureStaffMember($staff);

        $staff->assignedClasses()->detach($class->id);

        if ((int) $class->class_teacher_id === (int) $staff->id) {
            $class->update(['class_teacher_id' => null]);
        }

        return back()->with('success', 'Class unassigned successfully.');
    }

    private function ensureManager(Request $request, array $roles): void
    {
        $user = $request->user();

        abort_unless(
            $user && (in_array($user->role, $roles, true) || $user->can('students.manage')),
            403
        );
    }

    private function ensureStudentManager(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user && ($this->canManageAllStudentClasses($user) || $this->managedStudentClassIds($user)->isNotEmpty()),
            403
        );
    }

    private function canManageAllStudentClasses(User $user): bool
    {
        return in_array($user->role, ['admin', 'hod'], true) || $user->can('students.manage');
    }

    private function managedStudentClassIds(User $user)
    {
        if ($this->canManageAllStudentClasses($user)) {
            return collect();
        }

        return SchoolClass::query()
            ->where('class_teacher_id', $user->id)
            ->orWhereHas('teachers', fn ($query) => $query->where('users.id', $user->id))
            ->orWhereHas('assignedStaff', fn ($query) => $query->where('users.id', $user->id))
            ->pluck('id')
            ->filter()
            ->map(fn ($classId) => (int) $classId)
            ->unique()
            ->values();
    }

    private function ensureCanManageStudentClass(Request $request, int $classId): void
    {
        $user = $request->user();

        if ($user && $this->canManageAllStudentClasses($user)) {
            return;
        }

        abort_unless(
            $user && $classId > 0 && $this->managedStudentClassIds($user)->contains($classId),
            403,
            'You can only manage students in classes assigned to you.'
        );
    }

    private function defaultTeacherStudentClass(Request $request): void
    {
        $user = $request->user();

        if (!$user || $this->canManageAllStudentClasses($user) || $request->filled('school_class_id')) {
            return;
        }

        $classIds = $this->managedStudentClassIds($user);

        if ($classIds->count() === 1) {
            $request->merge(['school_class_id' => $classIds->first()]);
        }
    }

    private function ensureStaffMember(User $staff): void
    {
        abort_unless(in_array($staff->role, ['teacher', 'hod', 'cbt_personnel'], true), 404);
    }

    private function ensureStudentMember(User $student): void
    {
        abort_unless(in_array($student->role, ['student', 'prefect'], true), 404);
    }

    private function ensureSubjectsBelongToClass(array $subjectIds, int $classId): void
    {
        if (empty($subjectIds)) {
            return;
        }

        $validCount = Subject::whereIn('id', $subjectIds)
            ->where('school_class_id', $classId)
            ->count();

        if ($validCount !== count(array_unique($subjectIds))) {
            throw ValidationException::withMessages([
                'subject_ids' => 'Only subjects for the selected class can be assigned to this student.',
            ]);
        }
    }

    private function routePrefix(Request $request): string
    {
        $routeName = (string) $request->route()->getName();

        return match (true) {
            str_starts_with($routeName, 'hod.') => 'hod',
            str_starts_with($routeName, 'cbt.') => 'cbt',
            str_starts_with($routeName, 'teacher.') => 'teacher',
            default => 'admin',
        };
    }

    private function applyNameSearch($query, string $search): void
    {
        $driver = DB::connection()->getDriverName();
        $fullNameExpression = in_array($driver, ['mysql', 'mariadb'], true)
            ? "CONCAT(first_name, ' ', last_name) like ?"
            : "(first_name || ' ' || last_name) like ?";

        $query->where(function ($query) use ($search, $fullNameExpression) {
            $query->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('portal_id', 'like', "%{$search}%")
                ->orWhereRaw($fullNameExpression, ["%{$search}%"]);
        });
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return [$parts[0], $parts[1] ?? ''];
    }

    private function createProfile(Request $request, User $user, array $extra = []): Profile
    {
        $profile = $user->profile()->create($extra);

        if ($request->hasFile('profile_picture')) {
            $profile->updateProfilePicture($request->file('profile_picture'));
        }

        return $profile;
    }

    private function syncTeachingSubjects(User $user, array $subjectIds): void
    {
        DB::table('teacher_class_subject')->where('teacher_id', $user->id)->delete();

        Subject::whereIn('id', $subjectIds)->get()->each(function (Subject $subject) use ($user) {
            DB::table('teacher_class_subject')->insert([
                'teacher_id' => $user->id,
                'school_class_id' => $subject->school_class_id,
                'subject_id' => $subject->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    private function syncDirectPermissions(User $user, array $permissions): void
    {
        collect($permissions)->each(fn (string $permission) => Permission::firstOrCreate([
            'name' => $permission,
            'guard_name' => 'web',
        ]));

        $user->syncPermissions($permissions);
    }
}
