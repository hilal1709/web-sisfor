<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING MATERIALS STATUS ===\n\n";

// Check active materials
$activeMaterials = App\Models\Material::select('id', 'title', 'deleted_at')->get();
echo "Active Materials: " . $activeMaterials->count() . "\n";
foreach ($activeMaterials as $material) {
    echo "- ID: {$material->id}, Title: {$material->title}\n";
}

echo "\n";

// Check soft deleted materials
$deletedMaterials = App\Models\Material::onlyTrashed()->select('id', 'title', 'deleted_at')->get();
echo "Soft Deleted Materials: " . $deletedMaterials->count() . "\n";
foreach ($deletedMaterials as $material) {
    echo "- ID: {$material->id}, Title: {$material->title}, Deleted: {$material->deleted_at}\n";
}

echo "\n";

// Check all materials (including deleted)
$allMaterials = App\Models\Material::withTrashed()->select('id', 'title', 'deleted_at')->get();
echo "Total Materials (including deleted): " . $allMaterials->count() . "\n";

echo "\n=== CHECKING COURSES ===\n";
$courses = App\Models\Course::select('id', 'name')->get();
echo "Available Courses: " . $courses->count() . "\n";
foreach ($courses as $course) {
    echo "- ID: {$course->id}, Name: {$course->name}\n";
}