<?php

/**
 * Check form submissions and their user relationships
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking Form Submissions and Users\n";
echo "=====================================\n\n";

// Get all submissions
$submissions = \App\Models\FormSubmission::with('user')->take(10)->get();

echo "Total submissions checked: " . $submissions->count() . "\n\n";

$withUser = 0;
$withoutUser = 0;

foreach ($submissions as $submission) {
    $hasUser = $submission->user_id !== null && $submission->user !== null;
    
    if ($hasUser) {
        $withUser++;
        echo "✅ ID: {$submission->id} | user_id: {$submission->user_id} | User: {$submission->user->name}\n";
    } else {
        $withoutUser++;
        echo "❌ ID: {$submission->id} | user_id: " . ($submission->user_id ?? 'NULL') . " | User: NULL\n";
    }
}

echo "\n📊 Summary:\n";
echo "   ✅ Submissions with user: {$withUser}\n";
echo "   ❌ Submissions without user: {$withoutUser}\n\n";

// Check if user_id column exists and has data
echo "🔍 Checking database structure...\n";
$tableSchema = \Illuminate\Support\Facades\DB::select("DESCRIBE form_submissions");

foreach ($tableSchema as $column) {
    if ($column->Field === 'user_id') {
        echo "✅ Column 'user_id' exists\n";
        echo "   Type: {$column->Type}\n";
        echo "   Null: {$column->Null}\n";
        echo "   Default: " . ($column->Default ?? 'NULL') . "\n";
    }
}

echo "\n🔍 Checking actual data...\n";
$stats = \Illuminate\Support\Facades\DB::select("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN user_id IS NULL THEN 1 ELSE 0 END) as null_users,
        SUM(CASE WHEN user_id IS NOT NULL THEN 1 ELSE 0 END) as has_users
    FROM form_submissions
")[0];

echo "   Total submissions: {$stats->total}\n";
echo "   With user_id: {$stats->has_users}\n";
echo "   Without user_id (NULL): {$stats->null_users}\n";

echo "\n✅ Check completed\n";
