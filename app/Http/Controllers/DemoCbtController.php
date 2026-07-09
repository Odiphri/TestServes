<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\LoginController;
use App\Models\DemoRequest;
use App\Support\TenantDatabaseManager;
use Illuminate\Http\Request;

class DemoCbtController extends Controller
{
    public function showLogin(Request $request, string $demoRequest, string $expires, string $accessToken)
    {
        $demo = $this->validatedDemo($demoRequest, $expires, $accessToken);
        $this->startDemoSession($request, $demo);

        return view('auth.login', [
            'loginAction' => route('demo-cbt.login.submit', [$demoRequest, $expires, $accessToken]),
            'demoRequest' => $demo,
        ]);
    }

    public function login(Request $request, string $demoRequest, string $expires, string $accessToken, LoginController $controller)
    {
        $demo = $this->validatedDemo($demoRequest, $expires, $accessToken);
        $this->startDemoSession($request, $demo);

        return $controller->login($request);
    }

    private function validatedDemo(string $token, string $expires, string $accessToken): DemoRequest
    {
        $demo = DemoRequest::with('school')
            ->where('demo_token', $token)
            ->where('demo_access_token', $accessToken)
            ->firstOrFail();

        abort_unless($demo->isDemoAccessActive(), 403);
        abort_unless($demo->demo_expires_at?->format('YmdHis') === $expires, 403);
        abort_unless($demo->school, 404);

        return $demo;
    }

    private function startDemoSession(Request $request, DemoRequest $demo): void
    {
        app(TenantDatabaseManager::class)->activate($demo->school);

        app()->instance('currentSchool', $demo->school);
        view()->share('currentSchool', $demo->school);

        $request->session()->put([
            'demo_cbt_request_id' => $demo->id,
            'demo_cbt_school_id' => $demo->school_id,
            'demo_cbt_login_url' => $demo->demo_url,
        ]);
    }
}
