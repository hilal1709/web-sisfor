<?php

// Set up storage directories for Vercel
$storagePath = '/tmp/storage';
if (!is_dir($storagePath)) {
    mkdir($storagePath, 0755, true);
    mkdir($storagePath . '/framework', 0755, true);
    mkdir($storagePath . '/framework/cache', 0755, true);
    mkdir($storagePath . '/framework/sessions', 0755, true);
    mkdir($storagePath . '/framework/views', 0755, true);
    mkdir($storagePath . '/logs', 0755, true);
}

// Set environment variables for Laravel
$_ENV['APP_STORAGE_PATH'] = $storagePath;

// Include the main Laravel application
require __DIR__ . '/../public/index.php';