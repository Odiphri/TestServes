<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SuperAdmin\Concerns\AuthorizesPlatformSections;
use App\Models\SchoolOwner;
use App\Support\PlatformActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SchoolOwnerController extends Controller
{
    use AuthorizesPlatformSections;

    public function index(Request $request)
    {
        $owners = SchoolOwner::with('school')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(fn ($inner) => $inner
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('school', fn ($school) => $school->where('name', 'like', "%{$search}%")));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('super-admin.school-owners.index', compact('owners'));
    }

    public function show(SchoolOwner $schoolOwner)
    {
        $schoolOwner->load(['school.plan', 'school.branding']);

        return view('super-admin.school-owners.show', compact('schoolOwner'));
    }

    public function edit(SchoolOwner $schoolOwner)
    {
        $this->requireSuperAdmin();

        return view('super-admin.school-owners.edit', compact('schoolOwner'));
    }

    public function update(Request $request, SchoolOwner $schoolOwner)
    {
        $this->requireSuperAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('school_owners', 'email')->ignore($schoolOwner->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['active', 'disabled', 'pending'])],
        ]);

        $schoolOwner->update($data);
        PlatformActivity::log('owner_updated', "Updated owner {$schoolOwner->name}.", $schoolOwner);

        return redirect()->route('super-admin.school-owners.show', $schoolOwner)->with('success', 'School owner updated.');
    }

    public function updateStatus(SchoolOwner $schoolOwner, string $status)
    {
        $this->requireSuperAdmin();
        abort_unless(in_array($status, ['active', 'disabled', 'pending'], true), 404);

        $schoolOwner->update(['status' => $status]);
        PlatformActivity::log('owner_status_updated', "Changed owner {$schoolOwner->name} status to {$status}.", $schoolOwner);

        return back()->with('success', 'Owner status updated.');
    }

    public function resetPassword(SchoolOwner $schoolOwner)
    {
        $this->requireSuperAdmin();

        $password = 'Owner-'.Str::random(8);
        $schoolOwner->update(['password' => $password, 'status' => 'active']);
        PlatformActivity::log('owner_password_reset', "Reset password for owner {$schoolOwner->email}.", $schoolOwner);

        return back()->with('success', "Temporary owner password: {$password}");
    }

    public function destroy(SchoolOwner $schoolOwner)
    {
        $this->requireSuperAdmin();

        $name = $schoolOwner->name;
        $email = $schoolOwner->email;
        $schoolOwner->delete();
        PlatformActivity::log('owner_deleted', "Deleted owner {$email}.", $schoolOwner);

        return redirect()->route('super-admin.school-owners.index')->with('success', "Deleted owner {$name}.");
    }
}
