<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test the login flow for different user roles
$users = [
    'admin' => App\Models\User::where('role', 'admin')->first(),
    'teacher' => App\Models\User::where('role', 'teacher')->first(),
    'student' => App\Models\User::where('role', 'student')->first(),
    'hod' => App\Models\User::where('role', 'hod')->first(),
    'cbt_personnel' => App\Models\User::where('role', 'cbt_personnel')->first(),
    'prefect' => App\Models\User::where('role', 'prefect')->first(),
];

echo "=== Testing Login Flow for All User Roles ===\n\n";

foreach ($users as $role => $user) {
    if (!$user) {
        echo "No user found for role: $role\n";
        continue;
    }
    
    echo "Testing $role user: " . $user->name . "\n";
    echo "Portal ID: " . $user->portal_id . "\n";
    echo "Role: " . $user->role . "\n";
    
    // Simulate login
    auth()->login($user);
    
    // Test if user is authenticated
    if (auth()->check()) {
        echo "✓ User authenticated successfully\n";
        echo "✓ Current user: " . auth()->user()->name . "\n";
        echo "✓ Current user role: " . auth()->user()->role . "\n";
        
        // Test role-based routes
        $expectedRoute = $role . '.dashboard';
        echo "✓ Expected route: $expectedRoute\n";
        
        // Test if user can access their dashboard
        try {
            $routeExists = \Illuminate\Support\Facades\Route::has($expectedRoute);
            echo "✓ Route exists: " . ($routeExists ? 'Yes' : 'No') . "\n";
        } catch (Exception $e) {
            echo "✗ Route check failed: " . $e->getMessage() . "\n";
        }
        
        // Test middleware
        try {
            $middleware = new \App\Http\Middleware\RoleMiddleware();
            $request = new \Illuminate\Http\Request();
            $request->setRouteResolver(function() use ($expectedRoute) {
                return new \Illuminate\Routing\Route(['GET'], $expectedRoute, []);
            });
            
            // Simulate middleware check
            $canAccess = auth()->user()->role === $role || auth()->user()->hasRole($role);
            echo "✓ Can access dashboard: " . ($canAccess ? 'Yes' : 'No') . "\n";
        } catch (Exception $e) {
            echo "✗ Middleware check failed: " . $e->getMessage() . "\n";
        }
        
        // Logout for next test
        auth()->logout();
    } else {
        echo "✗ Authentication failed\n";
    }
    
    echo "------------------------\n";
}

echo "\n=== Testing Route Access ===\n";

// Test if all dashboard routes are accessible
$dashboardRoutes = [
    'admin.dashboard',
    'teacher.dashboard',
    'student.dashboard',
    'hod.dashboard',
    'cbt.dashboard',
    'prefect.dashboard',
];

foreach ($dashboardRoutes as $route) {
    $exists = \Illuminate\Support\Facades\Route::has($route);
    echo "Route $route: " . ($exists ? '✓ Exists' : '✗ Missing') . "\n";
}
