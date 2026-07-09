<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SubscriptionPlan;
use App\Support\TenantDatabaseManager;
use App\Support\TestServesDomains;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        $owner = Auth::guard('school_owner')->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('school_owners', 'email')->ignore($owner->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_profile_picture' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_profile_picture') && $owner->profile_picture) {
            Storage::disk('public')->delete($owner->profile_picture);
            $data['profile_picture'] = null;
        } elseif ($request->hasFile('profile_picture')) {
            if ($owner->profile_picture) {
                Storage::disk('public')->delete($owner->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('profile-pictures/owners', 'public');
        } else {
            unset($data['profile_picture']);
        }

        unset($data['remove_profile_picture']);
        $owner->update($data);

        return back()->with('success', 'Your profile has been updated.');
    }

    public function updateSchool(Request $request)
    {
        $owner = Auth::guard('school_owner')->user();
        $school = $owner->school;

        abort_unless($school, 404);

        $data = $request->validate([
            'school_name' => ['nullable', 'string', 'max:255'],
            'school_slug' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('schools', 'slug')->ignore($school->id)],
            'school_address' => ['nullable', 'string', 'max:1000'],
            'school_type' => ['nullable', Rule::in(['Nursery', 'Primary', 'Secondary', 'Combined'])],
            'expected_students_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
        ]);

        $slug = filled($data['school_slug'] ?? null) ? Str::slug($data['school_slug']) : $school->slug;

        $school->update([
            'name' => $data['school_name'] ?: $school->name,
            'slug' => $slug,
            'portal_url' => TestServesDomains::portalUrl($slug),
            'address' => $data['school_address'] ?? null,
            'school_type' => $data['school_type'] ?? null,
            'expected_students_count' => $data['expected_students_count'] ?? null,
            'contact_email' => $data['contact_email'] ?? $owner->email,
            'contact_phone' => $data['contact_phone'] ?? $owner->phone,
        ]);

        app(TenantDatabaseManager::class)->createAndMigrate($school);

        return back()->with('success', 'School setup details saved. You can still edit them later.');
    }

    public function updateBranding(Request $request)
    {
        $owner = Auth::guard('school_owner')->user();
        $school = $owner->school;

        abort_unless($school, 404);

        $data = $request->validate([
            'portal_display_name' => ['nullable', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:30'],
            'primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $branding = $school->branding()->firstOrCreate(['school_id' => $school->id]);

        if ($request->boolean('remove_logo') && $branding->logo_path) {
            Storage::disk('public')->delete($branding->logo_path);
            $data['logo_path'] = null;
        } elseif ($request->hasFile('logo')) {
            if ($branding->logo_path) {
                Storage::disk('public')->delete($branding->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('school-logos', 'public');
        }

        unset($data['logo'], $data['remove_logo']);
        $clean = array_filter($data, fn ($value, $key) => $key === 'logo_path' || ($value !== null && $value !== ''), ARRAY_FILTER_USE_BOTH);
        $branding->update($clean);

        return back()->with('success', 'Branding saved. You can polish it again anytime.');
    }

    public function updatePlan(Request $request)
    {
        $owner = Auth::guard('school_owner')->user();
        $school = $owner->school;

        abort_unless($school, 404);

        $data = $request->validate([
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
        ]);

        $school->update(['subscription_plan_id' => $data['subscription_plan_id'] ?? null]);
        $school->subscriptions()->latest()->first()?->update(['subscription_plan_id' => $data['subscription_plan_id'] ?? null]);

        return back()->with('success', 'Plan preference saved.');
    }

    public function plans()
    {
        return Schema::hasTable('subscription_plans')
            ? SubscriptionPlan::where('status', 'active')->orderBy('monthly_price')->get()
            : collect();
    }
}
