<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Role;
use App\Models\User;

echo "=== SYNCING ALL USER ROLES TO SPATIE ===\n\n";

// Get all users with a user_role set
$allUsers = User::whereNotNull('user_role')->get();

echo "📊 Found {$allUsers->count()} users with user_role set\n\n";

// Get all available Spatie roles
$availableRoles = Role::pluck('name')->toArray();
echo "🔧 Available Spatie roles: " . implode(', ', $availableRoles) . "\n\n";

$syncedCount = 0;
$skippedCount = 0;
$errorCount = 0;

foreach ($allUsers as $user) {
    echo "👤 Processing: {$user->name} ({$user->email})\n";
    echo "   Current user_role: '{$user->user_role}'\n";
    
    // Check current Spatie roles
    $currentRoles = $user->roles->pluck('name')->toArray();
    echo "   Current Spatie roles: [" . implode(', ', $currentRoles) . "]\n";
    
    // Check if the user_role exists as a Spatie role
    if (in_array($user->user_role, $availableRoles)) {
        try {
            // Sync the role
            $user->syncRoles([$user->user_role]);
            echo "   ✅ Synced to Spatie role: '{$user->user_role}'\n";
            $syncedCount++;
        } catch (Exception $e) {
            echo "   ❌ Error syncing: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    } else {
        echo "   ⚠️  Spatie role '{$user->user_role}' does not exist - skipping\n";
        $skippedCount++;
    }
    
    echo "\n";
}

echo "=== SYNC SUMMARY ===\n";
echo "✅ Successfully synced: {$syncedCount} users\n";
echo "⚠️  Skipped (role not found): {$skippedCount} users\n";
echo "❌ Errors: {$errorCount} users\n";
echo "📊 Total processed: " . ($syncedCount + $skippedCount + $errorCount) . " users\n\n";

// Show final verification
echo "=== VERIFICATION ===\n";
$verifiedUsers = User::whereNotNull('user_role')->with('roles')->get();

foreach ($verifiedUsers as $user) {
    $roleNames = $user->roles->pluck('name')->toArray();
    $isMatched = in_array($user->user_role, $roleNames) ? '✅' : '❌';
    echo "{$isMatched} {$user->name}: user_role='{$user->user_role}' | spatie_roles=[" . implode(', ', $roleNames) . "]\n";
}

echo "\n🎉 Sync complete!\n";