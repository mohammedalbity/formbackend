<?php
/**
 * اختبار سريع لـ Admin API
 * 
 * استخدام:
 * 1. تأكد من تشغيل: php artisan serve
 * 2. شغل هذا الملف: php test-admin-api.php
 */

// اختبار 1: Health Check
echo "=== Test 1: Health Check ===\n";
$healthResponse = file_get_contents('http://127.0.0.1:8000/api/v1/health');
echo $healthResponse . "\n\n";

// اختبار 2: Login as Admin
echo "=== Test 2: Login as Admin ===\n";
$loginData = json_encode([
    'email' => 'admin@formio.com',
    'password' => 'admin123456'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $loginData
    ]
]);

$loginResponse = file_get_contents('http://127.0.0.1:8000/api/v1/login', false, $context);
$loginResult = json_decode($loginResponse, true);

if (isset($loginResult['data']['token'])) {
    $token = $loginResult['data']['token'];
    echo "✅ Login successful!\n";
    echo "Token: " . substr($token, 0, 20) . "...\n\n";
    
    // اختبار 3: Get Users (Admin Only)
    echo "=== Test 3: Get Admin Users ===\n";
    $usersContext = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer $token\r\n"
        ]
    ]);
    
    $usersResponse = file_get_contents('http://127.0.0.1:8000/api/v1/admin/users', false, $usersContext);
    $usersResult = json_decode($usersResponse, true);
    
    if (isset($usersResult['success']) && $usersResult['success']) {
        echo "✅ Users API works!\n";
        echo "Total users: " . $usersResult['data']['total'] . "\n";
        echo "Current page: " . $usersResult['data']['current_page'] . "\n";
        echo "Users in this page: " . count($usersResult['data']['data']) . "\n\n";
        
        // عرض أول مستخدم
        if (!empty($usersResult['data']['data'])) {
            $firstUser = $usersResult['data']['data'][0];
            echo "First user:\n";
            echo "  - ID: " . $firstUser['id'] . "\n";
            echo "  - Name: " . $firstUser['name'] . "\n";
            echo "  - Email: " . $firstUser['email'] . "\n";
            echo "  - Role: " . $firstUser['role'] . "\n";
            echo "  - Active: " . ($firstUser['is_active'] ? 'Yes' : 'No') . "\n";
        }
    } else {
        echo "❌ Users API failed!\n";
        echo json_encode($usersResult, JSON_PRETTY_PRINT) . "\n";
    }
    
} else {
    echo "❌ Login failed!\n";
    echo "Response: " . $loginResponse . "\n";
}

echo "\n=== Tests Complete ===\n";
