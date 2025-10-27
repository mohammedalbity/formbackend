<?php

/**
 * Check form structures to see why field analysis is empty
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking Form Structures\n";
echo "========================\n\n";

$forms = \App\Models\Form::all();

echo "Total forms: " . $forms->count() . "\n\n";

foreach ($forms as $form) {
    echo "Form ID: {$form->id}\n";
    echo "Title: {$form->title}\n";
    
    if ($form->structure === null) {
        echo "❌ Structure: NULL\n";
    } else {
        echo "✅ Structure: EXISTS\n";
        
        $structure = is_string($form->structure) ? json_decode($form->structure, true) : $form->structure;
        
        if (is_array($structure)) {
            echo "   Type: array\n";
            echo "   Keys: " . implode(', ', array_keys($structure)) . "\n";
            
            // Check for components
            if (isset($structure['components'])) {
                echo "   ✅ Has components: " . count($structure['components']) . "\n";
            } elseif (isset($structure['pages'])) {
                echo "   ✅ Has pages (wizard): " . count($structure['pages']) . "\n";
            } else {
                echo "   ⚠️ No components or pages found\n";
            }
        } else {
            echo "   ⚠️ Structure is not an array\n";
        }
    }
    
    // Check submissions
    $submissionsCount = \App\Models\FormSubmission::where('form_id', $form->id)->count();
    echo "   Submissions: {$submissionsCount}\n";
    
    echo "\n";
}

echo "\n📊 Summary:\n";
$withStructure = \App\Models\Form::whereNotNull('structure')->count();
$withoutStructure = \App\Models\Form::whereNull('structure')->count();

echo "   Forms with structure: {$withStructure}\n";
echo "   Forms without structure: {$withoutStructure}\n";

echo "\n✅ Check completed\n";
