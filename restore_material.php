<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Restore the soft deleted material
$deletedMaterial = App\Models\Material::onlyTrashed()->find(7);

if ($deletedMaterial) {
    $deletedMaterial->restore();
    echo "Material ID 7 '{$deletedMaterial->title}' has been restored successfully!\n";
} else {
    echo "No deleted material found with ID 7.\n";
}

// Check current status
$materials = App\Models\Material::select('id', 'title', 'deleted_at')->get();
echo "\nCurrent active materials: " . $materials->count() . "\n";
foreach ($materials as $material) {
    echo "- ID: {$material->id}, Title: {$material->title}\n";
}