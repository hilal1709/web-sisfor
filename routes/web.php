<?php

use App\Http\Controllers\MaterialController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Google OAuth routes
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::view('/auth/google/setup', 'auth.google-setup')->name('auth.google.setup');
Route::view('/auth/google/debug', 'auth.google-debug')->name('auth.google.debug');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Avatar routes
    Route::post('/profile/avatar', [AvatarController::class, 'update'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [AvatarController::class, 'destroy'])->name('profile.avatar.destroy');

    // Material routes
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/create', [MaterialController::class, 'create'])->name('materials.create');
    Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store');
    Route::get('/materials/saved', [MaterialController::class, 'saved'])->name('materials.saved');
    Route::get('/materials/{material}', [MaterialController::class, 'show'])->name('materials.show');
    Route::get('/materials/{material}/edit', [MaterialController::class, 'edit'])->name('materials.edit');
    Route::put('/materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
    Route::post('/materials/{material}/toggle-save', [MaterialController::class, 'toggleSave'])->name('materials.toggle-save');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
    
    // Discussion routes
    Route::get('/discussions/my', [DiscussionController::class, 'myDiscussions'])->name('discussions.my');
    Route::post('/discussions', [DiscussionController::class, 'store'])->name('discussions.store');
    Route::delete('/discussions/{discussion}', [DiscussionController::class, 'destroy'])->name('discussions.destroy');

    // Notification page
    Route::view('/notifications', 'notifications.index')->name('notifications.index');
    // Analytics page
    Route::view('/analytics', 'analytics.index')->name('analytics.index');
});

require __DIR__.'/auth.php';
