<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking forms table columns\n";
echo "============================\n\n";

$columns = \Illuminate\Support\Facades\DB::select('DESCRIBE forms');

foreach ($columns as $col) {
    echo "Column: {$col->Field}\n";
    echo "  Type: {$col->Type}\n";
    echo "  Null: {$col->Null}\n";
    echo "  Default: " . ($col->Default ?? 'NULL') . "\n";
    echo "\n";
}

echo "Looking for 'structure' column...\n";
$hasStructure = false;
foreach ($columns as $col) {
    if ($col->Field === 'structure') {
        $hasStructure = true;
        echo "✅ Found 'structure' column!\n";
        break;
    }
}

if (!$hasStructure) {
    echo "❌ 'structure' column NOT found!\n";
    echo "   This is why field analysis is empty.\n";
}

echo "\n✅ Check completed\n";
