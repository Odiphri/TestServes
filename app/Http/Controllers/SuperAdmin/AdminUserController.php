<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SuperAdmin\Concerns\AuthorizesPlatformSections;
use App\Models\PlatformAdmin;
use App\Support\PlatformActivity;
use App\Support\PlatformAdminAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    use AuthorizesPlatformSections;

    public function index(Request $request)
    {
        $this->requireSuperAdmin();
        $search = trim((string) $request->query('search'));

        return view('super-admin.admin-users.index', [
            'admins' => PlatformAdmin::query()
                ->when($search !== '', fn ($query) => $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                }))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        $this->requireSuperAdmin();

        return view('super-admin.admin-users.create', ['admin' => new PlatformAdmin()]);
    }

    public function store(Request $request)
    {
        $this->requireSuperAdmin();

        $data = $this->validated($request);
        $admin = PlatformAdmin::create($data);
        PlatformActivity::log('admin_user_created', "Created platform admin {$admin->email}.", $admin);

        return redirect()->route('super-admin.admin-users.index')->with('success', 'Admin user created.');
    }

    public function edit(PlatformAdmin $adminUser)
    {
        $this->requireSuperAdmin();

        return view('super-admin.admin-users.edit', ['admin' => $adminUser]);
    }

    public function update(Request $request, PlatformAdmin $adminUser)
    {
        $this->requireSuperAdmin();

        $adminUser->update($this->validated($request, $adminUser));
        PlatformActivity::log('admin_user_updated', "Updated platform admin {$adminUser->email}.", $adminUser);

        return redirect()->route('super-admin.admin-users.index')->with('success', 'Admin user updated.');
    }

    public function destroy(PlatformAdmin $adminUser)
    {
        $this->requireSuperAdmin();
        abort_if($adminUser->is($this->platformAdmin()), 422, 'You cannot delete your own admin user.');

        $adminUser->update(['is_active' => false]);
        $adminUser->delete();
        PlatformActivity::log('admin_user_deleted', "Deleted platform admin {$adminUser->email}.", $adminUser);

        return back()->with('success', 'Admin user deleted.');
    }

    public function toggle(PlatformAdmin $adminUser)
    {
        $this->requireSuperAdmin();
        abort_if($adminUser->is($this->platformAdmin()), 422, 'You cannot deactivate your own admin user.');

        $adminUser->update(['is_active' => ! $adminUser->is_active]);
        PlatformActivity::log('admin_user_status_updated', "Toggled platform admin {$adminUser->email}.", $adminUser);

        return back()->with('success', 'Admin user status updated.');
    }

    public function resetPassword(PlatformAdmin $adminUser)
    {
        $this->requireSuperAdmin();

        $password = 'Admin-'.Str::random(10);
        $adminUser->update(['password' => $password, 'is_active' => true]);
        PlatformActivity::log('admin_user_password_reset', "Reset platform admin password for {$adminUser->email}.", $adminUser);

        return back()->with('success', "Temporary admin password: {$password}");
    }

    private function validated(Request $request, ?PlatformAdmin $admin = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('platform_admins', 'email')->ignore($admin?->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', Rule::in(PlatformAdminAccess::roles())],
            'is_active' => ['nullable', 'boolean'],
        ];

        if (! $admin) {
            $rules['password'] = ['required', 'string', 'min:8'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8'];
        }

        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active');

        if (($data['password'] ?? '') === '') {
            unset($data['password']);
        }

        return $data;
    }
}
