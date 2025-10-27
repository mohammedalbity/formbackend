<?php

/**
 * Test Analytics API Response to check if user data is included
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Analytics API Response\n";
echo "===============================\n\n";

// Get first admin user
$user = \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::first();
echo "✅ Using user: {$user->name} (ID: {$user->id})\n";

// Authenticate
\Illuminate\Support\Facades\Auth::setUser($user);

// Get first form
$form = \App\Models\Form::first();
if (!$form) {
    echo "❌ No forms found\n";
    exit(1);
}

echo "✅ Testing form: {$form->title} (ID: {$form->id})\n\n";

// Create a test submission with user_id
echo "📝 Creating test submission...\n";
$testSubmission = \App\Models\FormSubmission::create([
    'form_id' => $form->id,
    'data' => ['test_field' => 'test value'],
    'user_id' => $user->id,
    'submitted_at' => now(),
]);

echo "✅ Created submission ID: {$testSubmission->id}\n";
echo "   user_id: {$testSubmission->user_id}\n";
echo "   user name: " . ($testSubmission->user ? $testSubmission->user->name : 'NULL') . "\n\n";

// Call the analytics controller
$controller = new \App\Http\Controllers\Api\FormAnalyticsController();
$request = new \Illuminate\Http\Request();

echo "🔍 Calling Analytics API...\n";
$response = $controller->getFormAnalytics($request, $form->id);
$data = $response->getData(true);

if (!$data['success']) {
    echo "❌ API Error: " . ($data['message'] ?? 'Unknown') . "\n";
    exit(1);
}

echo "✅ API Response successful\n\n";

// Check recent_submissions
echo "📊 Recent Submissions:\n";
echo "   Total: " . count($data['data']['recent_submissions']) . "\n\n";

foreach ($data['data']['recent_submissions'] as $index => $submission) {
    echo "Submission #" . ($index + 1) . ":\n";
    echo "   ID: {$submission['id']}\n";
    echo "   submitted_at: {$submission['submitted_at']}\n";
    echo "   user_id: " . ($submission['user_id'] ?? 'NULL') . "\n";
    
    if (isset($submission['user'])) {
        echo "   ✅ user object exists:\n";
        echo "      - id: " . ($submission['user']['id'] ?? 'N/A') . "\n";
        echo "      - name: " . ($submission['user']['name'] ?? 'N/A') . "\n";
        echo "      - email: " . ($submission['user']['email'] ?? 'N/A') . "\n";
    } else {
        echo "   ❌ user object is NULL\n";
    }
    echo "\n";
}

// Check if our test submission is in the response
$foundTest = false;
foreach ($data['data']['recent_submissions'] as $submission) {
    if ($submission['id'] === $testSubmission->id) {
        $foundTest = true;
        echo "✅ Our test submission is in the response\n";
        if (isset($submission['user']) && $submission['user'] !== null) {
            echo "✅ And it has user data: {$submission['user']['name']}\n";
        } else {
            echo "❌ But it doesn't have user data!\n";
        }
        break;
    }
}

if (!$foundTest) {
    echo "⚠️ Our test submission is not in the recent 10\n";
}

echo "\n✅ Test completed\n";
