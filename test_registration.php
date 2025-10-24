<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UserType;
use App\Actions\Fortify\CreateNewUser;

echo "=== REGISTRATION DEBUG TEST ===\n";

// Test 1: Check user types in database
echo "1. Available user types in database:\n";
$userTypes = UserType::all();
foreach ($userTypes as $type) {
    echo "   ID: {$type->id}, Name: {$type->name}\n";
}

// Test 2: Test getUserTypeId function logic
echo "\n2. Testing user type ID mapping:\n";
$typeIds = [
    'resident' => 3,
    'volunteer' => 2,
    'admin' => 1,
];

foreach ($typeIds as $type => $id) {
    echo "   Type: {$type} -> ID: {$id}\n";
    $userType = UserType::find($id);
    if ($userType) {
        echo "   ✓ Found user type: {$userType->name}\n";
    } else {
        echo "   ✗ User type not found for ID: {$id}\n";
    }
}

// Test 3: Test CreateNewUser with sample data
echo "\n3. Testing CreateNewUser with sample data:\n";
$sampleInput = [
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'user_type_id' => 2, // volunteer
    'first_name' => 'John',
    'last_name' => 'Doe',
    'organization_id' => 1,
    // Removed application_notes as column was dropped
    'terms' => true,
];

echo "   Sample input: " . json_encode($sampleInput) . "\n";

try {
    $createNewUser = new CreateNewUser();
    echo "   ✓ CreateNewUser instance created successfully\n";
} catch (Exception $e) {
    echo "   ✗ Error creating CreateNewUser: " . $e->getMessage() . "\n";
}

echo "\n=== END DEBUG TEST ===\n";
