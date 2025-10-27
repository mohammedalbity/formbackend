<?php

/**
 * Test script for Form Analytics API
 * Usage: php test-analytics-api.php
 */

require __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get form ID from command line or use default
$formId = $argv[1] ?? 1;

echo "Testing Form Analytics API\n";
echo "=========================\n\n";

try {
    // Get a user (first admin or any user)
    $user = \App\Models\User::where('role', 'admin')->first() 
            ?? \App\Models\User::first();
    
    if (!$user) {
        echo "❌ No users found in database\n";
        exit(1);
    }

    echo "✅ Using user: {$user->name} (ID: {$user->id})\n\n";

    // Get form
    $form = \App\Models\Form::find($formId);
    
    if (!$form) {
        echo "❌ Form with ID {$formId} not found\n";
        exit(1);
    }

    echo "✅ Found form: {$form->title}\n";
    echo "   Form ID: {$form->id}\n";
    echo "   Owner: {$form->user->name}\n\n";

    // Check structure
    echo "📋 Form Structure:\n";
    if ($form->structure) {
        $structure = is_string($form->structure) ? json_decode($form->structure, true) : $form->structure;
        echo "   Type: " . gettype($structure) . "\n";
        if (is_array($structure)) {
            echo "   Keys: " . implode(', ', array_keys($structure)) . "\n";
            
            if (isset($structure['components'])) {
                echo "   Components count: " . count($structure['components']) . "\n";
            } elseif (isset($structure['pages'])) {
                echo "   Pages count: " . count($structure['pages']) . "\n";
            }
        }
    } else {
        echo "   ⚠️ Structure is null\n";
    }
    echo "\n";

    // Get submissions
    $submissions = \App\Models\FormSubmission::where('form_id', $formId)->get();
    echo "📊 Submissions: {$submissions->count()}\n\n";

    if ($submissions->count() > 0) {
        echo "Sample submission data:\n";
        $sample = $submissions->first();
        echo "   ID: {$sample->id}\n";
        echo "   User: " . ($sample->user ? $sample->user->name : 'Anonymous') . "\n";
        echo "   Status: {$sample->status}\n";
        echo "   Data keys: " . implode(', ', array_keys($sample->data)) . "\n";
        echo "\n";
    }

    // Test the controller
    echo "🧪 Testing FormAnalyticsController...\n";
    
    $controller = new \App\Http\Controllers\Api\FormAnalyticsController();
    
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    
    // Authenticate the user
    \Illuminate\Support\Facades\Auth::setUser($user);
    
    // Call the controller method
    $response = $controller->getFormAnalytics($request, $formId);
    $data = $response->getData(true);
    
    if ($data['success']) {
        echo "✅ API Response successful\n\n";
        
        echo "Basic Stats:\n";
        echo "   Total Submissions: " . $data['data']['basic_stats']['total_submissions'] . "\n";
        echo "   Status Breakdown: " . count($data['data']['basic_stats']['status_breakdown']) . " statuses\n";
        echo "\n";
        
        echo "Field Analysis:\n";
        echo "   Total fields analyzed: " . count($data['data']['field_analysis']) . "\n";
        
        foreach ($data['data']['field_analysis'] as $index => $field) {
            echo "\n   Field #" . ($index + 1) . ":\n";
            echo "      Key: {$field['field_key']}\n";
            echo "      Label: {$field['field_label']}\n";
            echo "      Type: {$field['field_type']}\n";
            echo "      Total Responses: {$field['total_responses']}\n";
            
            if (isset($field['yes_count'])) {
                echo "      Yes: {$field['yes_count']}, No: {$field['no_count']}\n";
            }
            
            if (isset($field['value_distribution'])) {
                echo "      Distribution: " . count($field['value_distribution']) . " values\n";
                foreach (array_slice($field['value_distribution'], 0, 3) as $value => $count) {
                    echo "         - {$value}: {$count}\n";
                }
            }
            
            if (isset($field['min'])) {
                echo "      Min: {$field['min']}, Max: {$field['max']}, Avg: {$field['average']}\n";
            }
        }
        
    } else {
        echo "❌ API Error: " . ($data['message'] ?? 'Unknown error') . "\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n   Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n✅ Test completed\n";
