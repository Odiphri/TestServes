<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test the actual login process
echo "=== Testing Browser Login Process ===\n\n";

// Get test users
$admin = App\Models\User::where('role', 'admin')->first();
$teacher = App\Models\User::where('role', 'teacher')->first();
$student = App\Models\User::where('role', 'student')->first();

if (!$admin || !$teacher || !$student) {
    echo "Missing test users!\n";
    exit;
}

// Test login credentials
$testCredentials = [
    'admin' => ['portal_id' => $admin->portal_id, 'password' => 'password'],
    'teacher' => ['portal_id' => $teacher->portal_id, 'password' => 'password'],
    'student' => ['portal_id' => $student->portal_id, 'password' => 'password'],
];

foreach ($testCredentials as $role => $credentials) {
    echo "Testing $role login:\n";
    echo "Portal ID: " . $credentials['portal_id'] . "\n";
    
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    $request->merge($credentials);
    
    // Test if credentials are valid
    $user = App\Models\User::where('portal_id', $credentials['portal_id'])->first();
    if ($user && \Hash::check($credentials['password'], $user->password)) {
        echo "✓ Credentials are valid\n";
        echo "✓ User role: " . $user->role . "\n";
        
        // Test if user can be authenticated
        if (auth()->attempt($credentials)) {
            echo "✓ Authentication successful\n";
            echo "✓ Authenticated user: " . auth()->user()->name . "\n";
            echo "✓ Authenticated user role: " . auth()->user()->role . "\n";
            
            // Test redirect logic
            $loginController = new \App\Http\Controllers\Auth\LoginController();
            $redirectResponse = $loginController->authenticated($request, $user);
            echo "✓ Redirect target: " . $redirectResponse->getTargetUrl() . "\n";
            
            // Logout for next test
            auth()->logout();
        } else {
            echo "✗ Authentication failed\n";
        }
    } else {
        echo "✗ Invalid credentials\n";
    }
    
    echo "------------------------\n";
}

// Test the HomeController redirect logic
echo "\n=== Testing HomeController Redirect Logic ===\n";

foreach (['admin', 'teacher', 'student', 'hod', 'cbt_personnel', 'prefect'] as $role) {
    $user = App\Models\User::where('role', $role)->first();
    if ($user) {
        auth()->login($user);
        
        $homeController = new \App\Http\Controllers\HomeController();
        $redirectResponse = $homeController->index();
        
        echo "Role: $role - Redirect: " . $redirectResponse->getTargetUrl() . "\n";
        
        auth()->logout();
    }
}

echo "\n=== Testing Direct Route Access ===\n";

// Test if routes are properly configured
$routes = [
    'admin.dashboard' => '/admin/dashboard',
    'teacher.dashboard' => '/teacher/dashboard',
    'student.dashboard' => '/student/dashboard',
    'hod.dashboard' => '/hod/dashboard',
    'cbt.dashboard' => '/cbt/dashboard',
    'prefect.dashboard' => '/prefect/dashboard',
];

foreach ($routes as $routeName => $routePath) {
    $exists = \Illuminate\Support\Facades\Route::has($routeName);
    echo "Route $routeName ($routePath): " . ($exists ? '✓' : '✗') . "\n";
}
