<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test session and authentication
echo "=== Testing Session and Authentication ===\n\n";

// Test if session is working
echo "Session driver: " . config('session.driver') . "\n";
echo "Session lifetime: " . config('session.lifetime') . " minutes\n";

// Test authentication with different users
$users = [
    'admin' => App\Models\User::where('role', 'admin')->first(),
    'teacher' => App\Models\User::where('role', 'teacher')->first(),
    'student' => App\Models\User::where('role', 'student')->first(),
];

foreach ($users as $role => $user) {
    if (!$user) {
        echo "No user found for role: $role\n";
        continue;
    }
    
    echo "\n--- Testing $role ---\n";
    echo "User: " . $user->name . "\n";
    echo "Portal ID: " . $user->portal_id . "\n";
    echo "Role: " . $user->role . "\n";
    
    // Test manual authentication
    if (auth()->login($user)) {
        echo "✓ Manual login successful\n";
        echo "✓ Auth user: " . auth()->user()->name . "\n";
        echo "✓ Auth role: " . auth()->user()->role . "\n";
        
        // Test if user can access their dashboard route
        $routeName = $role . '.dashboard';
        if ($role === 'cbt_personnel') {
            $routeName = 'cbt.dashboard';
        }
        
        echo "✓ Dashboard route: $routeName\n";
        
        // Test middleware
        try {
            $middleware = new \App\Http\Middleware\RoleMiddleware();
            $request = new \Illuminate\Http\Request();
            
            // Test if middleware allows access
            $canAccess = auth()->user()->role === $role || auth()->user()->hasRole($role);
            echo "✓ Middleware access: " . ($canAccess ? 'Allowed' : 'Denied') . "\n";
        } catch (Exception $e) {
            echo "✗ Middleware error: " . $e->getMessage() . "\n";
        }
        
        // Test HomeController redirect
        $homeController = new \App\Http\Controllers\HomeController();
        $redirect = $homeController->index();
        echo "✓ Home redirect: " . $redirect->getTargetUrl() . "\n";
        
        // Logout
        auth()->logout();
        echo "✓ Logged out\n";
    } else {
        echo "✗ Manual login failed\n";
    }
}

echo "\n=== Testing Password Verification ===\n";

// Test if passwords are correct
foreach ($users as $role => $user) {
    if ($user) {
        $passwordCheck = \Hash::check('password', $user->password);
        echo "$role password check: " . ($passwordCheck ? '✓' : '✗') . "\n";
    }
}

echo "\n=== Testing Route Definitions ===\n";

$routes = [
    'admin.dashboard' => 'admin',
    'teacher.dashboard' => 'teacher', 
    'student.dashboard' => 'student',
    'hod.dashboard' => 'hod',
    'cbt.dashboard' => 'cbt_personnel',
    'prefect.dashboard' => 'prefect',
];

foreach ($routes as $routeName => $expectedRole) {
    $exists = \Illuminate\Support\Facades\Route::has($routeName);
    echo "Route $routeName: " . ($exists ? '✓' : '✗') . " (expects $expectedRole)\n";
}
