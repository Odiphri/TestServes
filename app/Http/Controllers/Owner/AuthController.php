<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolOwner;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Support\TestServesDomains;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('platform_admin')->check()) {
            return redirect()->route('super-admin.dashboard');
        }

        if (Auth::guard('school_owner')->check()) {
            return redirect()->route('platform.dashboard');
        }

        return view('owner.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('platform_admin')->attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            Auth::guard('platform_admin')->user()->update(['last_login_at' => now()]);
            $request->session()->regenerate();

            return redirect()->intended(route('super-admin.dashboard'));
        }

        if (Auth::guard('school_owner')->attempt($credentials + ['status' => 'active'], $request->boolean('remember'))) {
            Auth::guard('school_owner')->user()->update(['last_login_at' => now()]);
            $request->session()->regenerate();

            return redirect()->intended(route('platform.dashboard'));
        }

        return back()
            ->withErrors(['email' => 'These credentials do not match an owner or platform admin account.'])
            ->onlyInput('email');
    }

    public function showRegister()
    {
        return view('owner.auth.register', [
            'plans' => $this->plans(),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('school_owners', 'email')],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'school_slug' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('schools', 'slug')],
            'school_address' => ['nullable', 'string', 'max:1000'],
            'school_type' => ['nullable', Rule::in(['Nursery', 'Primary', 'Secondary', 'Combined'])],
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
        ]);

        $owner = DB::transaction(function () use ($data) {
            $schoolName = filled($data['school_name'] ?? null) ? $data['school_name'] : "{$data['name']}'s school setup";
            $slug = filled($data['school_slug'] ?? null)
                ? Str::slug($data['school_slug'])
                : $this->uniqueSetupSlug($data['name']);

            $school = School::create([
                'subscription_plan_id' => $data['subscription_plan_id'] ?? null,
                'name' => $schoolName,
                'slug' => $slug,
                'portal_url' => TestServesDomains::portalUrl($slug),
                'address' => $data['school_address'] ?? null,
                'school_type' => $data['school_type'] ?? null,
                'status' => 'pending',
                'contact_email' => $data['email'],
                'contact_phone' => $data['phone'] ?? null,
            ]);

            $owner = SchoolOwner::create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'is_primary' => true,
            ]);

            $school->branding()->create([
                'primary_color' => '#2563eb',
                'secondary_color' => '#0f172a',
                'accent_color' => '#22c55e',
                'short_name' => Str::upper(Str::substr($school->name, 0, 12)),
                'portal_display_name' => $school->name,
            ]);

            $school->subscriptions()->create([
                'subscription_plan_id' => $data['subscription_plan_id'] ?? null,
                'status' => 'pending',
            ]);

            return $owner;
        });

        Auth::guard('school_owner')->login($owner);
        $request->session()->regenerate();

        return redirect()->route('platform.dashboard')
            ->with('success', 'Your owner account is ready. You can finish the school setup now or later.');
    }

    public function logout(Request $request)
    {
        Auth::guard('school_owner')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.login')->with('status', 'You have been logged out.');
    }

    private function plans()
    {
        return Schema::hasTable('subscription_plans')
            ? SubscriptionPlan::where('status', 'active')->orderBy('monthly_price')->get()
            : collect();
    }

    private function uniqueSetupSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'school';
        $slug = $base;
        $count = 1;

        while (School::where('slug', $slug)->exists()) {
            $slug = "{$base}-setup-{$count}";
            $count++;
        }

        return $slug;
    }
}
