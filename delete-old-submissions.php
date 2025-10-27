<?php

/**
 * Delete old submissions without user_id
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Delete Old Submissions (NULL user_id)\n";
echo "======================================\n\n";

// Count before
$count = \App\Models\FormSubmission::whereNull('user_id')->count();
echo "📊 Found {$count} submissions with NULL user_id\n\n";

if ($count === 0) {
    echo "✅ No submissions to delete\n";
    exit(0);
}

echo "⚠️  This will DELETE {$count} submissions!\n";
echo "Type 'yes' to confirm: ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$confirmation = trim($line);

if (strtolower($confirmation) !== 'yes') {
    echo "\n❌ Cancelled\n";
    exit(0);
}

echo "\n🗑️  Deleting...\n";

$deleted = \App\Models\FormSubmission::whereNull('user_id')->delete();

echo "✅ Deleted {$deleted} submissions\n";
echo "\n✅ Done! Now all submissions in analytics will have user names.\n";
