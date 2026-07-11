<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AcademicManagementController;
use App\Http\Controllers\AcademicSessionController;
use App\Http\Controllers\BursaryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PrefectRoleController;
use App\Http\Controllers\StudentRoleController;
use App\Http\Controllers\TrafficAnalyticsController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\LiveSupportController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\CBT\DashboardController as CBTDashboard;
use App\Http\Controllers\CBT\ExamController as CBTExamController;
use App\Http\Controllers\CBT\MonitorController as CBTMonitorController;
use App\Http\Controllers\HOD\DashboardController as HODDashboard;
use App\Http\Controllers\HOD\ExamController as HODExamController;
use App\Http\Controllers\HOD\OverrideController as HODOverrideController;
use App\Http\Controllers\HOD\StudentController as HODStudentController;
use App\Http\Controllers\Prefect\DashboardController as PrefectDashboard;
use App\Http\Controllers\Prefect\StudentController as PrefectStudentController;
use App\Http\Controllers\Student\DirectoryController as StudentDirectoryController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Student\ExamController as StudentExamController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\RequestController as StudentRequestController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboard;
use App\Http\Controllers\Teacher\ExamController as TeacherExamController;
use App\Http\Controllers\Teacher\QuestionController as TeacherQuestionController;
use App\Http\Controllers\Teacher\ResultController as TeacherResultController;
use App\Http\Controllers\Teacher\ProfileController as TeacherProfileController;
use App\Http\Controllers\Teacher\PromotionController as TeacherPromotionController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\Teacher\AIQuestionController as TeacherAIQuestionController;
use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\SuperAdmin\ComingSoonController as SuperAdminComingSoonController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SchoolController as SuperAdminSchoolController;
use App\Http\Controllers\SuperAdmin\SubscriptionPlanController as SuperAdminSubscriptionPlanController;
use App\Http\Controllers\SuperAdmin\SchoolOwnerController as SuperAdminSchoolOwnerController;
use App\Http\Controllers\SuperAdmin\PaymentRecordController as SuperAdminPaymentRecordController;
use App\Http\Controllers\SuperAdmin\PaymentDisputeController as SuperAdminPaymentDisputeController;
use App\Http\Controllers\SuperAdmin\SupportTicketController as SuperAdminSupportTicketController;
use App\Http\Controllers\SuperAdmin\LiveSupportController as SuperAdminLiveSupportController;
use App\Http\Controllers\SuperAdmin\ActivityLogController as SuperAdminActivityLogController;
use App\Http\Controllers\SuperAdmin\SystemSettingController as SuperAdminSystemSettingController;
use App\Http\Controllers\SuperAdmin\AdminUserController as SuperAdminAdminUserController;
use App\Http\Controllers\SuperAdmin\ProfileController as SuperAdminProfileController;
use App\Http\Controllers\Owner\AuthController as OwnerAuthController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\ProfileController as OwnerProfileController;
use App\Http\Controllers\Owner\PaymentController as OwnerPaymentController;
use App\Http\Controllers\Owner\PortalAdminController as OwnerPortalAdminController;

foreach (\App\Support\TestServesDomains::allRootDomains() as $portalRootDomain) {
    Route::domain('{school}.'.$portalRootDomain)->middleware('cbt.host')->group(function () use ($portalRootDomain) {
        Route::middleware('guest')->group(function () use ($portalRootDomain) {
            Route::get('/', [LoginController::class, 'showLoginForm'])->name($portalRootDomain === config('testserves.root_domain') ? 'login.portal-home' : 'login.portal-home.'.$portalRootDomain);
            Route::get('login', [LoginController::class, 'showLoginForm'])->name($portalRootDomain === config('testserves.root_domain') ? 'school.login' : 'school.login.'.$portalRootDomain);
            Route::post('login', [LoginController::class, 'login'])->name($portalRootDomain === config('testserves.root_domain') ? 'school.login.submit' : 'school.login.submit.'.$portalRootDomain);
        });
    });
}

Route::get('login', [OwnerAuthController::class, 'showLogin'])->name('platform.login');
Route::post('login', [OwnerAuthController::class, 'login'])->name('platform.login.submit');
Route::get('register', [OwnerAuthController::class, 'showRegister'])->name('platform.register');
Route::post('register', [OwnerAuthController::class, 'register'])->name('platform.register.submit');

Route::middleware('school.owner')->group(function () {
    Route::get('dashboard', [OwnerDashboardController::class, 'index'])->name('platform.dashboard');
    Route::get('profile', [OwnerDashboardController::class, 'profile'])->name('platform.profile');
    Route::get('school', [OwnerDashboardController::class, 'school'])->name('platform.school');
    Route::get('branding', [OwnerDashboardController::class, 'branding'])->name('platform.branding');
    Route::get('dashboard/branding', fn () => redirect()->route('platform.branding'))->name('platform.branding.legacy');
    Route::get('plans', [OwnerDashboardController::class, 'plans'])->name('platform.plans');
    Route::get('portal-admins', [OwnerPortalAdminController::class, 'index'])->name('platform.portal-admins');
    Route::post('portal-admins', [OwnerPortalAdminController::class, 'store'])->name('platform.portal-admins.store');
    Route::delete('portal-admins/{admin}', [OwnerPortalAdminController::class, 'destroy'])->name('platform.portal-admins.destroy');
    Route::get('payments', [OwnerPaymentController::class, 'index'])->name('platform.payments');
    Route::post('payments', [OwnerPaymentController::class, 'store'])->name('platform.payments.store');
    Route::delete('payments/{payment}', [OwnerPaymentController::class, 'destroy'])->name('platform.payments.destroy');
    Route::post('payments/trial', [OwnerPaymentController::class, 'startTrial'])->name('platform.trial.start');
    Route::post('payments/paystack', [OwnerPaymentController::class, 'initializePaystack'])->name('platform.payments.paystack');
    Route::get('payments/paystack/callback', [OwnerPaymentController::class, 'paystackCallback'])->name('platform.payments.paystack.callback');
    Route::put('dashboard/profile', [OwnerProfileController::class, 'updateProfile'])->name('platform.profile.update');
    Route::match(['post', 'put'], 'dashboard/school', [OwnerProfileController::class, 'updateSchool'])->name('platform.school.update');
    Route::match(['post', 'put'], 'dashboard/branding', [OwnerProfileController::class, 'updateBranding'])->name('platform.branding.update');
    Route::match(['post', 'put'], 'branding', [OwnerProfileController::class, 'updateBranding'])->name('platform.branding.save');
    Route::match(['post', 'put'], 'branding/update', [OwnerProfileController::class, 'updateBranding'])->name('platform.branding.update.legacy');
    Route::put('dashboard/plan', [OwnerProfileController::class, 'updatePlan'])->name('platform.plan.update');
    Route::post('dashboard/logout', [OwnerAuthController::class, 'logout'])->name('platform.logout');
});

Route::prefix('owner')->name('owner.')->group(function () {
    Route::get('login', fn () => redirect()->route('platform.login'))->name('login');
    Route::post('login', fn () => redirect()->route('platform.login'))->name('login.submit');
    Route::get('register', fn () => redirect()->route('platform.register'))->name('register');
    Route::post('register', fn () => redirect()->route('platform.register'))->name('register.submit');
    Route::get('dashboard', fn () => redirect()->route('platform.dashboard'))->name('dashboard');
    Route::get('branding', fn () => redirect()->route('platform.branding'))->name('branding');
    Route::match(['post', 'put'], 'branding', [OwnerProfileController::class, 'updateBranding'])->name('branding.update');
    Route::match(['post', 'put'], 'dashboard/branding', [OwnerProfileController::class, 'updateBranding'])->name('dashboard.branding.update');
    Route::post('logout', fn () => redirect()->route('platform.login'))->name('logout');
});

Route::view('/', 'landing')->name('platform.home');
Route::get('live-support', [LiveSupportController::class, 'create'])->name('live-support.create');
Route::post('live-support', [LiveSupportController::class, 'store'])->name('live-support.store');
Route::get('live-support/{token}', [LiveSupportController::class, 'show'])->name('live-support.show');
Route::post('live-support/{token}', [LiveSupportController::class, 'reply'])->name('live-support.reply');

Route::get('storage/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return response()->file(Storage::disk('public')->path($path));
})->where('path', '.*')->name('public-storage.fallback');

Route::post('_test/school/login', function (Illuminate\Http\Request $request, LoginController $controller) {
    abort_unless(app()->runningUnitTests() || app()->environment('testing'), 404);

    return $controller->login($request);
})->name('login.submit');

Route::get('_test/school/login', function (LoginController $controller) {
    abort_unless(app()->runningUnitTests() || app()->environment('testing'), 404);

    return $controller->showLoginForm();
})->name('login');

Route::prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('login', fn () => redirect()->route('platform.login'))->name('login');

    Route::middleware('platform.admin')->group(function () {
        Route::get('/', [SuperAdminDashboardController::class, 'index'])->middleware('platform.admin:dashboard')->name('dashboard');
        Route::post('logout', [SuperAdminAuthController::class, 'logout'])->name('logout');
        Route::get('profile', [SuperAdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [SuperAdminProfileController::class, 'update'])->name('profile.update');

        Route::resource('schools', SuperAdminSchoolController::class)->middleware('platform.admin:schools');
        Route::patch('schools/{school}/status/{status}', [SuperAdminSchoolController::class, 'updateStatus'])->name('schools.status');
        Route::post('schools/{school}/restore', [SuperAdminSchoolController::class, 'restore'])->name('schools.restore');
        Route::post('schools/{school}/reset-owner-password', [SuperAdminSchoolController::class, 'resetOwnerPassword'])->name('schools.reset-owner-password');

        Route::resource('school-owners', SuperAdminSchoolOwnerController::class)->only(['index', 'show', 'edit', 'update', 'destroy'])->middleware('platform.admin:school_owners');
        Route::patch('school-owners/{schoolOwner}/status/{status}', [SuperAdminSchoolOwnerController::class, 'updateStatus'])->middleware('platform.admin:school_owners')->name('school-owners.status');
        Route::post('school-owners/{schoolOwner}/reset-password', [SuperAdminSchoolOwnerController::class, 'resetPassword'])->middleware('platform.admin:school_owners')->name('school-owners.reset-password');

        Route::post('subscription-plans/ai-draft', [SuperAdminSubscriptionPlanController::class, 'generateDraft'])->middleware('platform.admin:subscription_plans')->name('subscription-plans.ai-draft');
        Route::resource('subscription-plans', SuperAdminSubscriptionPlanController::class)->except('show')->parameters(['subscription-plans' => 'plan'])->middleware('platform.admin:subscription_plans');
        Route::resource('plans', SuperAdminSubscriptionPlanController::class)->except('show')->middleware('platform.admin:subscription_plans');

        Route::resource('payments', SuperAdminPaymentRecordController::class)->parameters(['payments' => 'payment'])->middleware('platform.admin:payments');
        Route::patch('payments/{payment}/mark/{status}', [SuperAdminPaymentRecordController::class, 'markStatus'])->middleware('platform.admin:payments')->name('payments.mark-status');
        Route::resource('payment-disputes', SuperAdminPaymentDisputeController::class)
            ->parameters(['payment-disputes' => 'paymentDispute'])
            ->except('destroy')
            ->middleware('platform.admin:payment_disputes');
        Route::patch('payment-disputes/{paymentDispute}/mark/{status}', [SuperAdminPaymentDisputeController::class, 'mark'])->middleware('platform.admin:payment_disputes')->name('payment-disputes.mark');

        Route::resource('support-tickets', SuperAdminSupportTicketController::class)->except('destroy')->middleware('platform.admin:support_tickets');
        Route::get('live-support', [SuperAdminLiveSupportController::class, 'index'])->middleware('platform.admin:live_support')->name('live-support.index');
        Route::get('live-support/{liveSupport}', [SuperAdminLiveSupportController::class, 'show'])->middleware('platform.admin:live_support')->name('live-support.show');
        Route::post('live-support/{liveSupport}/reply', [SuperAdminLiveSupportController::class, 'reply'])->middleware('platform.admin:live_support')->name('live-support.reply');
        Route::patch('live-support/{liveSupport}', [SuperAdminLiveSupportController::class, 'update'])->middleware('platform.admin:live_support')->name('live-support.update');
        Route::get('activity-logs', [SuperAdminActivityLogController::class, 'index'])->middleware('platform.admin:activity_logs')->name('activity-logs.index');
        Route::get('system-settings', [SuperAdminSystemSettingController::class, 'index'])->middleware('platform.admin:system_settings')->name('system-settings.index');
        Route::post('system-settings', [SuperAdminSystemSettingController::class, 'update'])->middleware('platform.admin:system_settings')->name('system-settings.update');
        Route::resource('admin-users', SuperAdminAdminUserController::class)->parameters(['admin-users' => 'adminUser'])->except('show')->middleware('platform.admin:admin_users');
        Route::patch('admin-users/{adminUser}/toggle', [SuperAdminAdminUserController::class, 'toggle'])->middleware('platform.admin:admin_users')->name('admin-users.toggle');
        Route::post('admin-users/{adminUser}/reset-password', [SuperAdminAdminUserController::class, 'resetPassword'])->middleware('platform.admin:admin_users')->name('admin-users.reset-password');
    });
});

// Authentication Routes
Route::view('privacy-policy', 'privacy-policy')->name('privacy.policy');

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware(['cbt.host', 'auth', 'school.feature']);

// Password change routes
Route::middleware(['cbt.host', 'auth', 'school.feature'])->group(function () {
    Route::get('password/change', [ChangePasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('password/change', [ChangePasswordController::class, 'change'])->name('password.change.submit');
});

// Admin Routes
Route::middleware(['cbt.host', 'auth', 'school.feature', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::get('users', [AdminDashboard::class, 'users'])->name('users');
    Route::put('users/{user}/role', [AdminDashboard::class, 'updateUserRole'])->name('users.role.update');
    Route::delete('users/{user}', [AdminDashboard::class, 'destroyUser'])->name('users.destroy');
    Route::get('students', [UserManagementController::class, 'students'])->name('students');
    Route::post('students', [UserManagementController::class, 'storeStudent'])->name('students.store');
    Route::put('students/{student}', [UserManagementController::class, 'updateStudent'])->name('students.update');
    Route::delete('students/{student}', [UserManagementController::class, 'destroyStudent'])->name('students.destroy');
    Route::get('staff', [UserManagementController::class, 'staff'])->name('staff');
    Route::post('staff', [UserManagementController::class, 'storeStaff'])->name('staff.store');
    Route::put('staff/{staff}', [UserManagementController::class, 'updateStaff'])->name('staff.update');
    Route::delete('staff/{staff}', [UserManagementController::class, 'destroyStaff'])->name('staff.destroy');
    Route::post('staff/{staff}/classes', [UserManagementController::class, 'assignClass'])->name('staff.classes.assign');
    Route::delete('staff/{staff}/classes/{class}', [UserManagementController::class, 'unassignClass'])->name('staff.classes.unassign');
    Route::get('classes', [AcademicManagementController::class, 'classes'])->name('classes');
    Route::post('classes', [AcademicManagementController::class, 'storeClass'])->name('classes.store');
    Route::put('classes/{class}', [AcademicManagementController::class, 'updateClass'])->name('classes.update');
    Route::delete('classes/{class}', [AcademicManagementController::class, 'destroyClass'])->name('classes.destroy');
    Route::get('subjects', [AcademicManagementController::class, 'subjects'])->name('subjects');
    Route::post('subjects', [AcademicManagementController::class, 'storeSubject'])->name('subjects.store');
    Route::delete('subjects/group', [AcademicManagementController::class, 'destroySubjectGroup'])->name('subjects.group.destroy');
    Route::put('subjects/{subject}', [AcademicManagementController::class, 'updateSubject'])->name('subjects.update');
    Route::delete('subjects/{subject}', [AcademicManagementController::class, 'destroySubject'])->name('subjects.destroy');
    Route::get('exams', [AdminDashboard::class, 'exams'])->name('exams');
    Route::get('exams/create', [AdminDashboard::class, 'examCreate'])->name('exams.create');
    Route::post('exams/store', [AdminDashboard::class, 'examStore'])->name('exams.store');
    Route::get('exams/{exam}', [AdminDashboard::class, 'examShow'])->name('exams.show');
    Route::get('exams/{exam}/edit', [AdminDashboard::class, 'examEdit'])->name('exams.edit');
    Route::put('exams/{exam}', [AdminDashboard::class, 'examUpdate'])->name('exams.update');
    Route::delete('exams/{exam}', [AdminDashboard::class, 'examDestroy'])->name('exams.destroy');
    Route::post('exams/{exam}/toggle-live', [AdminDashboard::class, 'toggleExamLive'])->name('exams.toggle-live');
    Route::post('exams/{exam}/generate-questions', [AdminDashboard::class, 'generateQuestions'])->name('exams.generate-questions');
    Route::post('exams/{exam}/add-question', [AdminDashboard::class, 'addManualQuestion'])->name('exams.add-question');
    Route::delete('exam-questions/{question}', [AdminDashboard::class, 'deleteQuestion'])->name('exam-questions.delete');
    Route::delete('questions/{question}', [AdminDashboard::class, 'questionDestroy'])->name('questions.destroy');
    Route::get('monitor', [CBTMonitorController::class, 'index'])->name('monitor');
    Route::get('monitor/data', [CBTMonitorController::class, 'data'])->name('monitor.data');
    Route::get('payments', [BursaryController::class, 'index'])->name('payments');
    Route::get('payments/students/{student}', [BursaryController::class, 'showStudent'])->name('payments.students.show');
    Route::post('payments/fees', [BursaryController::class, 'storeFee'])->name('payments.fees.store');
    Route::patch('payments/fees/{feeItem}/toggle', [BursaryController::class, 'toggleFee'])->name('payments.fees.toggle');
    Route::delete('payments/fees/{feeItem}', [BursaryController::class, 'destroyFee'])->name('payments.fees.destroy');
    Route::put('payments/students/{student}', [BursaryController::class, 'recordPayment'])->name('payments.students.update');
    Route::post('payments/students/{student}/optional-fees/{feeItem}/remove', [BursaryController::class, 'removeOptionalFee'])->name('payments.optional-fees.remove');
    Route::delete('payments/students/{student}/optional-fees/{feeItem}/restore', [BursaryController::class, 'restoreOptionalFee'])->name('payments.optional-fees.restore');
    Route::get('overrides', [HODOverrideController::class, 'index'])->name('overrides');
    Route::post('overrides', [HODOverrideController::class, 'store'])->name('overrides.store');
    Route::delete('overrides/{override}', [HODOverrideController::class, 'destroy'])->name('overrides.destroy');
    Route::get('reports', [AdminDashboard::class, 'reports'])->name('reports');
    Route::get('settings', [AdminDashboard::class, 'settings'])->name('settings');
    Route::post('settings', [AdminDashboard::class, 'updateSettings'])->name('settings.save');
    Route::post('settings/update', [AdminDashboard::class, 'updateSettings'])->name('settings.update');
    Route::get('profile', [TeacherProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [TeacherProfileController::class, 'update'])->name('profile.update');
});

// HOD Routes
Route::middleware(['cbt.host', 'auth', 'school.feature', 'role:hod'])->prefix('hod')->name('hod.')->group(function () {
    Route::get('dashboard', [HODDashboard::class, 'index'])->name('dashboard');
    Route::get('exams', [HODExamController::class, 'index'])->name('exams');
    Route::get('students', [UserManagementController::class, 'students'])->name('students');
    Route::post('students', [UserManagementController::class, 'storeStudent'])->name('students.store');
    Route::put('students/{student}', [UserManagementController::class, 'updateStudent'])->name('students.update');
    Route::delete('students/{student}', [UserManagementController::class, 'destroyStudent'])->name('students.destroy');
    Route::get('staff', [UserManagementController::class, 'staff'])->name('staff');
    Route::post('staff', [UserManagementController::class, 'storeStaff'])->name('staff.store');
    Route::put('staff/{staff}', [UserManagementController::class, 'updateStaff'])->name('staff.update');
    Route::delete('staff/{staff}', [UserManagementController::class, 'destroyStaff'])->name('staff.destroy');
    Route::post('staff/{staff}/classes', [UserManagementController::class, 'assignClass'])->name('staff.classes.assign');
    Route::delete('staff/{staff}/classes/{class}', [UserManagementController::class, 'unassignClass'])->name('staff.classes.unassign');
    Route::get('classes', [AcademicManagementController::class, 'classes'])->name('classes');
    Route::post('classes', [AcademicManagementController::class, 'storeClass'])->name('classes.store');
    Route::put('classes/{class}', [AcademicManagementController::class, 'updateClass'])->name('classes.update');
    Route::delete('classes/{class}', [AcademicManagementController::class, 'destroyClass'])->name('classes.destroy');
    Route::get('subjects', [AcademicManagementController::class, 'subjects'])->name('subjects');
    Route::post('subjects', [AcademicManagementController::class, 'storeSubject'])->name('subjects.store');
    Route::delete('subjects/group', [AcademicManagementController::class, 'destroySubjectGroup'])->name('subjects.group.destroy');
    Route::put('subjects/{subject}', [AcademicManagementController::class, 'updateSubject'])->name('subjects.update');
    Route::delete('subjects/{subject}', [AcademicManagementController::class, 'destroySubject'])->name('subjects.destroy');
    Route::get('payments', [BursaryController::class, 'index'])->name('payments');
    Route::get('payments/students/{student}', [BursaryController::class, 'showStudent'])->name('payments.students.show');
    Route::post('payments/fees', [BursaryController::class, 'storeFee'])->name('payments.fees.store');
    Route::patch('payments/fees/{feeItem}/toggle', [BursaryController::class, 'toggleFee'])->name('payments.fees.toggle');
    Route::delete('payments/fees/{feeItem}', [BursaryController::class, 'destroyFee'])->name('payments.fees.destroy');
    Route::put('payments/students/{student}', [BursaryController::class, 'recordPayment'])->name('payments.students.update');
    Route::post('payments/students/{student}/optional-fees/{feeItem}/remove', [BursaryController::class, 'removeOptionalFee'])->name('payments.optional-fees.remove');
    Route::delete('payments/students/{student}/optional-fees/{feeItem}/restore', [BursaryController::class, 'restoreOptionalFee'])->name('payments.optional-fees.restore');
    Route::get('exams/create', [CBTExamController::class, 'create'])->name('exams.create');
    Route::post('exams', [CBTExamController::class, 'store'])->name('exams.store');
    Route::get('exams/{exam}', [CBTExamController::class, 'show'])->name('exams.show');
    Route::get('exams/{exam}/edit', [CBTExamController::class, 'edit'])->name('exams.edit');
    Route::put('exams/{exam}', [CBTExamController::class, 'update'])->name('exams.update');
    Route::delete('exams/{exam}', [CBTExamController::class, 'destroy'])->name('exams.destroy');
    Route::post('exams/{exam}/toggle', [CBTExamController::class, 'toggleLive'])->name('exams.toggle');
    Route::post('exams/{exam}/toggle-live', [CBTExamController::class, 'toggleLive'])->name('exams.toggle-live');
    Route::post('exams/{exam}/toggle-results', [CBTExamController::class, 'toggleResults'])->name('exams.toggle-results');
    Route::post('exams/{exam}/generate-questions', [CBTExamController::class, 'generateQuestions'])->name('exams.generate-questions');
    Route::post('exams/{exam}/add-question', [CBTExamController::class, 'addManualQuestion'])->name('exams.add-question');
    Route::delete('exam-questions/{question}', [CBTExamController::class, 'deleteQuestion'])->name('exam-questions.delete');
    Route::get('monitor', [CBTMonitorController::class, 'index'])->name('monitor');
    Route::get('monitor/data', [CBTMonitorController::class, 'data'])->name('monitor.data');
    Route::get('results', [TeacherResultController::class, 'index'])->name('results');
    Route::get('results/{exam}', [TeacherResultController::class, 'show'])->name('results.show');
    Route::post('results/{exam}/export', [TeacherResultController::class, 'export'])->name('results.export');
    Route::delete('results/{exam}/attempts/{attempt}/retake', [TeacherResultController::class, 'allowRetake'])->name('results.retakes.allow');
    Route::get('overrides', [HODOverrideController::class, 'index'])->name('overrides');
    Route::post('overrides', [HODOverrideController::class, 'store'])->name('overrides.store');
    Route::delete('overrides/{override}', [HODOverrideController::class, 'destroy'])->name('overrides.destroy');
    Route::get('profile', [TeacherProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [TeacherProfileController::class, 'update'])->name('profile.update');
});

// CBT Personnel Routes
Route::middleware(['cbt.host', 'auth', 'school.feature', 'role:cbt_personnel'])->prefix('cbt')->name('cbt.')->group(function () {
    Route::get('dashboard', [CBTDashboard::class, 'index'])->name('dashboard');
    Route::get('students', [UserManagementController::class, 'students'])->name('students');
    Route::post('students', [UserManagementController::class, 'storeStudent'])->name('students.store');
    Route::put('students/{student}', [UserManagementController::class, 'updateStudent'])->name('students.update');
    Route::delete('students/{student}', [UserManagementController::class, 'destroyStudent'])->name('students.destroy');
    Route::get('exams', [CBTExamController::class, 'index'])->name('exams');
    Route::get('exams/create', [CBTExamController::class, 'create'])->name('exams.create');
    Route::post('exams', [CBTExamController::class, 'store'])->name('exams.store');
    Route::get('exams/{exam}', [CBTExamController::class, 'show'])->name('exams.show');
    Route::get('exams/{exam}/edit', [CBTExamController::class, 'edit'])->name('exams.edit');
    Route::put('exams/{exam}', [CBTExamController::class, 'update'])->name('exams.update');
    Route::delete('exams/{exam}', [CBTExamController::class, 'destroy'])->name('exams.destroy');
    Route::post('exams/{exam}/toggle', [CBTExamController::class, 'toggleLive'])->name('exams.toggle');
    Route::post('exams/{exam}/toggle-live', [CBTExamController::class, 'toggleLive'])->name('exams.toggle-live');
    Route::post('exams/{exam}/toggle-results', [CBTExamController::class, 'toggleResults'])->name('exams.toggle-results');
    Route::post('exams/{exam}/generate-questions', [CBTExamController::class, 'generateQuestions'])->name('exams.generate-questions');
    Route::post('exams/{exam}/add-question', [CBTExamController::class, 'addManualQuestion'])->name('exams.add-question');
    Route::delete('exam-questions/{question}', [CBTExamController::class, 'deleteQuestion'])->name('exam-questions.delete');
    Route::get('monitor', [CBTMonitorController::class, 'index'])->name('monitor');
    Route::get('monitor/data', [CBTMonitorController::class, 'data'])->name('monitor.data');
    Route::get('results', [TeacherResultController::class, 'index'])->name('results');
    Route::get('results/{exam}', [TeacherResultController::class, 'show'])->name('results.show');
    Route::post('results/{exam}/export', [TeacherResultController::class, 'export'])->name('results.export');
    Route::delete('results/{exam}/attempts/{attempt}/retake', [TeacherResultController::class, 'allowRetake'])->name('results.retakes.allow');
    Route::get('payments', [BursaryController::class, 'index'])->name('payments');
    Route::get('payments/students/{student}', [BursaryController::class, 'showStudent'])->name('payments.students.show');
    Route::post('payments/fees', [BursaryController::class, 'storeFee'])->name('payments.fees.store');
    Route::patch('payments/fees/{feeItem}/toggle', [BursaryController::class, 'toggleFee'])->name('payments.fees.toggle');
    Route::delete('payments/fees/{feeItem}', [BursaryController::class, 'destroyFee'])->name('payments.fees.destroy');
    Route::put('payments/students/{student}', [BursaryController::class, 'recordPayment'])->name('payments.students.update');
    Route::post('payments/students/{student}/optional-fees/{feeItem}/remove', [BursaryController::class, 'removeOptionalFee'])->name('payments.optional-fees.remove');
    Route::delete('payments/students/{student}/optional-fees/{feeItem}/restore', [BursaryController::class, 'restoreOptionalFee'])->name('payments.optional-fees.restore');
    Route::get('overrides', [HODOverrideController::class, 'index'])->name('overrides');
    Route::post('overrides', [HODOverrideController::class, 'store'])->name('overrides.store');
    Route::delete('overrides/{override}', [HODOverrideController::class, 'destroy'])->name('overrides.destroy');
    Route::get('profile', [TeacherProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [TeacherProfileController::class, 'update'])->name('profile.update');
});

// Teacher Routes
Route::middleware(['cbt.host', 'auth', 'school.feature', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('dashboard', [TeacherDashboard::class, 'index'])->name('dashboard');
    Route::get('classes', [TeacherClassController::class, 'index'])->name('classes');
    Route::get('students', [TeacherStudentController::class, 'index'])->name('students');
    Route::post('students', [UserManagementController::class, 'storeStudent'])->name('students.store');
    Route::put('students/{student}', [UserManagementController::class, 'updateStudent'])->name('students.update');
    Route::delete('students/{student}', [UserManagementController::class, 'destroyStudent'])->name('students.destroy');
    Route::get('exams', [TeacherExamController::class, 'index'])->name('exams');
    Route::get('exams/create', [TeacherExamController::class, 'create'])->name('exams.create');
    Route::post('exams', [TeacherExamController::class, 'store'])->name('exams.store');
    Route::get('exams/{exam}', [TeacherExamController::class, 'show'])->name('exams.show');
    Route::get('exams/{exam}/edit', [TeacherExamController::class, 'edit'])->name('exams.edit');
    Route::put('exams/{exam}', [TeacherExamController::class, 'update'])->name('exams.update');
    Route::delete('exams/{exam}', [TeacherExamController::class, 'destroy'])->name('exams.destroy');
    Route::post('exams/{exam}/toggle-live', [TeacherExamController::class, 'toggleLive'])->name('exams.toggle-live');
    Route::post('exams/{exam}/toggle-results', [TeacherExamController::class, 'toggleResults'])->name('exams.toggle-results');
    Route::post('exams/{exam}/generate-questions', [TeacherExamController::class, 'generateQuestions'])->name('exams.generate-questions');
    Route::post('exams/{exam}/add-question', [TeacherExamController::class, 'addManualQuestion'])->name('exams.add-question');
    Route::delete('exam-questions/{question}', [TeacherExamController::class, 'deleteQuestion'])->name('exam-questions.delete');
    Route::get('questions', [TeacherQuestionController::class, 'index'])->name('questions');
    Route::get('questions/create', [TeacherQuestionController::class, 'create'])->name('questions.create');
    Route::post('questions', [TeacherQuestionController::class, 'store'])->name('questions.store');
    Route::get('questions/{question}/edit', [TeacherQuestionController::class, 'edit'])->name('questions.edit');
    Route::put('questions/{question}', [TeacherQuestionController::class, 'update'])->name('questions.update');
    Route::delete('questions/{question}', [TeacherQuestionController::class, 'destroy'])->name('questions.destroy');
    Route::get('ai-questions', [TeacherAIQuestionController::class, 'index'])->name('ai-questions.index');
    Route::post('ai-questions/generate', [TeacherAIQuestionController::class, 'generate'])->name('ai-questions.generate');
    Route::get('ai-questions/exam/{examId}/questions', [TeacherAIQuestionController::class, 'getExamQuestions'])->name('ai-questions.exam-questions');
    Route::get('attendance', [TeacherAttendanceController::class, 'index'])->name('attendance');
    Route::post('attendance', [TeacherAttendanceController::class, 'store'])->name('attendance.store');
    Route::get('promotions', [TeacherPromotionController::class, 'index'])->name('promotions');
    Route::patch('promotions/{student}/promote', [TeacherPromotionController::class, 'promote'])->name('promotions.promote');
    Route::patch('promotions/{student}/demote', [TeacherPromotionController::class, 'demote'])->name('promotions.demote');
    Route::get('results', [TeacherResultController::class, 'index'])->name('results');
    Route::get('results/{exam}', [TeacherResultController::class, 'show'])->name('results.show');
    Route::post('results/{exam}/export', [TeacherResultController::class, 'export'])->name('results.export');
    Route::delete('results/{exam}/attempts/{attempt}/retake', [TeacherResultController::class, 'allowRetake'])->name('results.retakes.allow');
    Route::get('payments', [BursaryController::class, 'index'])->name('payments');
    Route::get('payments/students/{student}', [BursaryController::class, 'showStudent'])->name('payments.students.show');
    Route::post('payments/fees', [BursaryController::class, 'storeFee'])->name('payments.fees.store');
    Route::patch('payments/fees/{feeItem}/toggle', [BursaryController::class, 'toggleFee'])->name('payments.fees.toggle');
    Route::delete('payments/fees/{feeItem}', [BursaryController::class, 'destroyFee'])->name('payments.fees.destroy');
    Route::put('payments/students/{student}', [BursaryController::class, 'recordPayment'])->name('payments.students.update');
    Route::post('payments/students/{student}/optional-fees/{feeItem}/remove', [BursaryController::class, 'removeOptionalFee'])->name('payments.optional-fees.remove');
    Route::delete('payments/students/{student}/optional-fees/{feeItem}/restore', [BursaryController::class, 'restoreOptionalFee'])->name('payments.optional-fees.restore');
    Route::get('overrides', [HODOverrideController::class, 'index'])->name('overrides');
    Route::post('overrides', [HODOverrideController::class, 'store'])->name('overrides.store');
    Route::delete('overrides/{override}', [HODOverrideController::class, 'destroy'])->name('overrides.destroy');
    Route::get('profile', [TeacherProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [TeacherProfileController::class, 'update'])->name('profile.update');
});

// Prefect Routes
Route::middleware(['cbt.host', 'auth', 'school.feature', 'role:prefect'])->prefix('prefect')->name('prefect.')->group(function () {
    Route::get('dashboard', [PrefectDashboard::class, 'index'])->name('dashboard');
    Route::get('exams', [StudentExamController::class, 'index'])->name('exams');
    Route::get('exams/{exam}', [StudentExamController::class, 'show'])->name('exams.show');
    Route::post('exams/{exam}', [StudentExamController::class, 'store'])->name('exams.store');
    Route::post('exams/{exam}/store', [StudentExamController::class, 'store'])->name('exams.autosave');
    Route::post('exams/{exam}/submit', [StudentExamController::class, 'submit'])->name('exams.submit');
    Route::get('exams/{exam}/results', [StudentExamController::class, 'results'])->name('exams.results');
    Route::get('students', [PrefectStudentController::class, 'index'])->name('students');
    Route::get('students/{student}', [PrefectStudentController::class, 'show'])->name('students.show');
    Route::get('students/{student}/edit', [PrefectStudentController::class, 'edit'])->name('students.edit');
    Route::put('students/{student}', [PrefectStudentController::class, 'update'])->name('students.update');
});

// Student Routes. Prefects keep their own dashboard, but can use the same
// student academic and personal tools because prefects are also students.
Route::middleware(['cbt.host', 'auth', 'school.feature', 'role:student,prefect'])->prefix('student')->name('student.')->group(function () {
    Route::get('dashboard', [StudentDashboard::class, 'index'])->name('dashboard');
    Route::get('exams', [StudentExamController::class, 'index'])->name('exams');
    Route::get('exams/{exam}', [StudentExamController::class, 'show'])->name('exams.show');
    Route::post('exams/{exam}', [StudentExamController::class, 'store'])->name('exams.store');
    Route::post('exams/{exam}/store', [StudentExamController::class, 'store'])->name('exams.autosave');
    Route::post('exams/{exam}/submit', [StudentExamController::class, 'submit'])->name('exams.submit');
    Route::get('exams/{exam}/results', [StudentExamController::class, 'results'])->name('exams.results');
    Route::get('payments', [StudentDashboard::class, 'payments'])->name('payments');
    Route::get('attendance', [StudentDashboard::class, 'attendance'])->name('attendance');
    Route::get('directory/students', [StudentDirectoryController::class, 'students'])->name('directory.students');
    Route::get('directory/students/{student}', [StudentDirectoryController::class, 'student'])->name('directory.student');
    Route::get('profile', [StudentProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [StudentProfileController::class, 'update'])->name('profile.update');
    Route::get('requests', [StudentRequestController::class, 'index'])->name('requests');
    Route::post('requests', [StudentRequestController::class, 'store'])->name('requests.store');
});

// Common Routes (for all authenticated users)
Route::middleware(['cbt.host', 'auth', 'school.feature'])->group(function () {
    Route::get('home', [HomeController::class, 'index'])->name('home');
    Route::get('traffic', [TrafficAnalyticsController::class, 'index'])->name('traffic.index');
    Route::get('traffic/data', [TrafficAnalyticsController::class, 'data'])->name('traffic.data');
    Route::get('academic-sessions', [AcademicSessionController::class, 'index'])->name('academic-sessions.index');
    Route::post('academic-sessions', [AcademicSessionController::class, 'store'])->name('academic-sessions.store');
    Route::put('academic-sessions/{academicSession}', [AcademicSessionController::class, 'update'])->name('academic-sessions.update');
    Route::patch('academic-sessions/{academicSession}/activate', [AcademicSessionController::class, 'activate'])->name('academic-sessions.activate');
    Route::get('student-roles', [StudentRoleController::class, 'index'])->name('student-roles.index');
    Route::post('student-roles', [StudentRoleController::class, 'store'])->name('student-roles.store');
    Route::put('student-roles/{studentRole}', [StudentRoleController::class, 'update'])->name('student-roles.update');
    Route::delete('student-roles/{studentRole}', [StudentRoleController::class, 'destroy'])->name('student-roles.destroy');
    Route::get('prefect-roles', [PrefectRoleController::class, 'index'])->name('prefect-roles.index');
    Route::post('prefect-roles', [PrefectRoleController::class, 'store'])->name('prefect-roles.store');
    Route::put('prefect-roles/{prefectRole}', [PrefectRoleController::class, 'update'])->name('prefect-roles.update');
    Route::delete('prefect-roles/{prefectRole}', [PrefectRoleController::class, 'destroy'])->name('prefect-roles.destroy');
});
