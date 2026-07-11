<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\TrafficLogger;
use App\Support\DashboardRoute;
use App\Support\TestServesDomains;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'portal_id' => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only('portal_id', 'password');
    }

    protected function attemptLogin(Request $request)
    {
        $login = $request->input('portal_id');
        $user = User::query()
            ->where('portal_id', $login)
            ->orWhere('email', $login)
            ->first();

        if (! $user || ! $user->is_active || ! Hash::check($request->input('password'), $user->password)) {
            return false;
        }

        $this->guard()->login($user, $request->boolean('remember'));

        return true;
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'portal_id';
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Update last login
        $user->update(['last_login_at' => now()]);
        app(TrafficLogger::class)->start($request, $user);

        $school = app()->bound('currentSchool') ? app('currentSchool') : null;

        if ($school && in_array($school->status, ['deactivated', 'suspended', 'expired'], true)) {
            return response()->view('errors.school-account-locked', [
                'school' => $school,
                'user' => $user,
            ], 403);
        }

        // Only teachers are forced through first-login password changes.
        if ($user->role === 'teacher' && $user->must_change_password) {
            return redirect()->route('password.change');
        }

        // Redirect based on role
        return $this->redirectToRole($user);
    }

    public function logout(Request $request)
    {
        $inactive = $request->boolean('inactive');

        app(TrafficLogger::class)->end($request);

        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json([], 204);
        }

        $redirect = redirect()->to(TestServesDomains::schoolLoginUrl($request));

        return $inactive
            ? $redirect->with('status', 'You have been logged out due to inactivity.')
            : $redirect;
    }

    /**
     * Redirect user based on their role.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectToRole(User $user)
    {
        if ($user->can('bursary.manage')) {
            return redirect()->route($this->bursaryRouteFor($user->role));
        }

        return redirect()->route(DashboardRoute::forUser($user));
    }

    private function bursaryRouteFor(string $role): string
    {
        return match ($role) {
            'admin' => 'admin.payments',
            'hod' => 'hod.payments',
            'cbt_personnel' => 'cbt.payments',
            'teacher' => 'teacher.payments',
            default => 'home',
        };
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    }
