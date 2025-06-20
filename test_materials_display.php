<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Material;
use App\Models\Course;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Materials Display ===\n\n";

// Test 1: Check database connection and materials count
echo "1. Database Connection Test:\n";
try {
    $totalMaterials = DB::table('materials')->count();
    $activeMaterials = DB::table('materials')->whereNull('deleted_at')->count();
    $deletedMaterials = DB::table('materials')->whereNotNull('deleted_at')->count();
    
    echo "   ✅ Database connected successfully\n";
    echo "   📊 Total materials: {$totalMaterials}\n";
    echo "   ✅ Active materials: {$activeMaterials}\n";
    echo "   ❌ Deleted materials: {$deletedMaterials}\n\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Test Material model query
echo "2. Material Model Test:\n";
try {
    $materials = Material::whereNull('deleted_at')->with(['course', 'user'])->get();
    echo "   ✅ Material model query successful\n";
    echo "   📊 Materials retrieved: " . $materials->count() . "\n";
    
    if ($materials->count() > 0) {
        $firstMaterial = $materials->first();
        echo "   📝 First material: {$firstMaterial->title}\n";
        echo "   👤 Author: {$firstMaterial->user->name}\n";
        echo "   📚 Course: {$firstMaterial->course->name}\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Material model query failed: " . $e->getMessage() . "\n\n";
}

// Test 3: Test controller logic simulation
echo "3. Controller Logic Simulation:\n";
try {
    // Simulate the controller's index method query
    $query = Material::query()
        ->whereNull('deleted_at')
        ->with(['course', 'user', 'verifications']);
    
    $materials = $query->latest()->paginate(10);
    
    echo "   ✅ Controller query simulation successful\n";
    echo "   📊 Paginated materials: " . $materials->count() . "\n";
    echo "   📄 Total pages: " . $materials->lastPage() . "\n";
    echo "   🔢 Total items: " . $materials->total() . "\n\n";
} catch (Exception $e) {
    echo "   ❌ Controller simulation failed: " . $e->getMessage() . "\n\n";
}

// Test 4: Check for any materials with empty required fields
echo "4. Data Integrity Check:\n";
try {
    $materialsWithEmptyFields = DB::table('materials')
        ->whereNull('deleted_at')
        ->where(function($query) {
            $query->whereNull('title')
                  ->orWhere('title', '')
                  ->orWhereNull('description')
                  ->orWhere('description', '')
                  ->orWhereNull('fakultas')
                  ->orWhere('fakultas', '')
                  ->orWhereNull('jurusan')
                  ->orWhere('jurusan', '')
                  ->orWhereNull('mata_kuliah')
                  ->orWhere('mata_kuliah', '');
        })
        ->get();
    
    if ($materialsWithEmptyFields->isEmpty()) {
        echo "   ✅ All materials have required fields filled\n";
    } else {
        echo "   ⚠️  Found " . $materialsWithEmptyFields->count() . " materials with empty required fields:\n";
        foreach ($materialsWithEmptyFields as $material) {
            echo "      - ID: {$material->id}, Title: '{$material->title}'\n";
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Data integrity check failed: " . $e->getMessage() . "\n\n";
}

// Test 5: Check recent updates
echo "5. Recent Updates Check:\n";
try {
    $recentMaterials = DB::table('materials')
        ->whereNull('deleted_at')
        ->orderBy('updated_at', 'desc')
        ->limit(5)
        ->get(['id', 'title', 'updated_at']);
    
    echo "   📅 Recent materials (last 5 updated):\n";
    foreach ($recentMaterials as $material) {
        echo "      - ID: {$material->id}, Title: {$material->title}, Updated: {$material->updated_at}\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Recent updates check failed: " . $e->getMessage() . "\n\n";
}

echo "=== Test Complete ===\n";
echo "If all tests passed, the materials should display correctly on the website.\n";
echo "If you're still experiencing issues, please check:\n";
echo "1. Web server configuration\n";
echo "2. Laravel logs in storage/logs/\n";
echo "3. Browser cache and cookies\n";
echo "4. Network connectivity\n";