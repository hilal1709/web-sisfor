<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Material;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Material Edit Functionality ===\n\n";

// Test 1: Find a material to test with
echo "1. Finding test material:\n";
$testMaterial = Material::whereNull('deleted_at')->first();

if (!$testMaterial) {
    echo "   ❌ No materials found for testing\n";
    exit(1);
}

echo "   ✅ Found test material: {$testMaterial->title} (ID: {$testMaterial->id})\n";
echo "   📊 Original data:\n";
echo "      - Title: {$testMaterial->title}\n";
echo "      - Fakultas: {$testMaterial->fakultas}\n";
echo "      - Jurusan: {$testMaterial->jurusan}\n";
echo "      - Mata Kuliah: {$testMaterial->mata_kuliah}\n";
echo "      - Updated At: {$testMaterial->updated_at}\n";
echo "      - Deleted At: " . ($testMaterial->deleted_at ?? 'NULL') . "\n\n";

// Test 2: Simulate edit operation using direct DB update (like controller does)
echo "2. Simulating edit operation:\n";
try {
    $updateData = [
        'title' => $testMaterial->title . ' - TEST EDIT ' . date('H:i:s'),
        'description' => $testMaterial->description . ' [EDITED]',
        'fakultas' => $testMaterial->fakultas,
        'jurusan' => $testMaterial->jurusan,
        'mata_kuliah' => $testMaterial->mata_kuliah,
        'semester' => $testMaterial->semester,
        'kategori' => $testMaterial->kategori,
        'updated_at' => now(),
        'deleted_at' => null // Explicitly ensure material stays active
    ];

    $updated = DB::table('materials')
        ->where('id', $testMaterial->id)
        ->whereNull('deleted_at')
        ->update($updateData);

    if ($updated) {
        echo "   ✅ Edit operation successful\n";
        
        // Verify the material is still active
        $updatedMaterial = DB::table('materials')
            ->where('id', $testMaterial->id)
            ->whereNull('deleted_at')
            ->first();
        
        if ($updatedMaterial) {
            echo "   ✅ Material is still active after edit\n";
            echo "   📊 Updated data:\n";
            echo "      - Title: {$updatedMaterial->title}\n";
            echo "      - Updated At: {$updatedMaterial->updated_at}\n";
            echo "      - Deleted At: " . ($updatedMaterial->deleted_at ?? 'NULL') . "\n";
        } else {
            echo "   ❌ Material disappeared after edit!\n";
        }
    } else {
        echo "   ❌ Edit operation failed\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Edit simulation failed: " . $e->getMessage() . "\n\n";
}

// Test 3: Check if material appears in index query
echo "3. Testing index query after edit:\n";
try {
    $materials = Material::query()
        ->whereNull('deleted_at')
        ->with(['course', 'user'])
        ->latest()
        ->get();
    
    $editedMaterial = $materials->where('id', $testMaterial->id)->first();
    
    if ($editedMaterial) {
        echo "   ✅ Edited material appears in index query\n";
        echo "   📝 Title: {$editedMaterial->title}\n";
    } else {
        echo "   ❌ Edited material missing from index query\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Index query test failed: " . $e->getMessage() . "\n\n";
}

// Test 4: Check total materials count
echo "4. Final materials count:\n";
try {
    $totalMaterials = DB::table('materials')->count();
    $activeMaterials = DB::table('materials')->whereNull('deleted_at')->count();
    $deletedMaterials = DB::table('materials')->whereNotNull('deleted_at')->count();
    
    echo "   📊 Total materials: {$totalMaterials}\n";
    echo "   ✅ Active materials: {$activeMaterials}\n";
    echo "   ❌ Deleted materials: {$deletedMaterials}\n";
    
    if ($deletedMaterials > 0) {
        echo "   ⚠️  Warning: Some materials are soft deleted!\n";
        $deleted = DB::table('materials')->whereNotNull('deleted_at')->get(['id', 'title', 'deleted_at']);
        foreach ($deleted as $material) {
            echo "      - ID: {$material->id}, Title: {$material->title}, Deleted: {$material->deleted_at}\n";
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Count check failed: " . $e->getMessage() . "\n\n";
}

echo "=== Test Complete ===\n";
echo "Material edit functionality has been tested.\n";
echo "Check the results above to ensure materials don't disappear after editing.\n";