<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check all users and their roles
$users = App\Models\User::all();

echo "=== User Authentication Test ===\n";
echo "Total users: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo "User: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Role: " . $user->role . "\n";
    echo "Portal ID: " . $user->portal_id . "\n";
    echo "Has Role (Spatie): " . ($user->hasRole($user->role) ? 'Yes' : 'No') . "\n";
    echo "------------------------\n";
}

// Test authentication for a specific user
$admin = App\Models\User::where('role', 'admin')->first();
if ($admin) {
    echo "\n=== Testing Admin Authentication ===\n";
    echo "Admin user found: " . $admin->name . "\n";
    echo "Admin role: " . $admin->role . "\n";
    
    // Test if user can access admin dashboard
    try {
        auth()->login($admin);
        echo "User authenticated successfully\n";
        echo "Current authenticated user: " . auth()->user()->name . "\n";
        echo "Current authenticated user role: " . auth()->user()->role . "\n";
    } catch (Exception $e) {
        echo "Authentication error: " . $e->getMessage() . "\n";
    }
} else {
    echo "\nNo admin user found!\n";
}
