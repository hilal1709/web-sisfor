<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get all soft deleted materials
$deletedMaterials = App\Models\Material::onlyTrashed()->get();

if ($deletedMaterials->count() > 0) {
    echo "Found {$deletedMaterials->count()} deleted materials:\n";

    foreach ($deletedMaterials as $material) {
        echo "\nID: {$material->id}";
        echo "\nTitle: {$material->title}";
        echo "\nDeleted at: {$material->deleted_at}";
        echo "\n------------------------";
    }

    echo "\n\nRestoring all materials...";

    // Restore all materials
    App\Models\Material::onlyTrashed()->restore();

    echo "\nAll materials have been restored successfully!\n";
} else {
    echo "No deleted materials found.\n";
}

// Check current status
$materials = App\Models\Material::select('id', 'title', 'deleted_at')->get();
echo "\nCurrent active materials: " . $materials->count() . "\n";
