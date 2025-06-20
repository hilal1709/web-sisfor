<?php

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "🔍 Testing Google OAuth Configuration\n";
echo "=====================================\n\n";

// Check environment variables
$clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
$redirectUri = $_ENV['GOOGLE_REDIRECT_URI'] ?? '';
$appUrl = $_ENV['APP_URL'] ?? '';

echo "📋 Configuration Check:\n";
echo "Client ID: " . ($clientId ? "✅ Set (" . substr($clientId, 0, 20) . "...)" : "❌ Not set") . "\n";
echo "Client Secret: " . ($clientSecret ? "✅ Set (" . substr($clientSecret, 0, 10) . "...)" : "❌ Not set") . "\n";
echo "Redirect URI: " . ($redirectUri ? "✅ " . $redirectUri : "❌ Not set") . "\n";
echo "App URL: " . ($appUrl ? "✅ " . $appUrl : "❌ Not set") . "\n\n";

if (!$clientId || !$clientSecret) {
    echo "❌ Google OAuth is not properly configured!\n";
    echo "Please check your .env file.\n";
    exit(1);
}

// Test Google OAuth URL generation
try {
    $config = [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri' => str_replace('${APP_URL}', $appUrl, $redirectUri),
    ];
    
    $authUrl = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
        'client_id' => $config['client_id'],
        'redirect_uri' => $config['redirect_uri'],
        'scope' => 'openid profile email',
        'response_type' => 'code',
        'access_type' => 'offline',
        'prompt' => 'consent',
    ]);
    
    echo "🔗 Generated OAuth URL:\n";
    echo $authUrl . "\n\n";
    
    echo "✅ Google OAuth configuration appears to be valid!\n\n";
    
    echo "📚 Next Steps:\n";
    echo "1. Make sure your Google Cloud Console has these settings:\n";
    echo "   - Authorized JavaScript origins: " . $appUrl . "\n";
    echo "   - Authorized redirect URIs: " . $config['redirect_uri'] . "\n";
    echo "2. Test the login at: " . $appUrl . "/login\n";
    echo "3. If you get errors, check the Laravel logs\n";
    
} catch (Exception $e) {
    echo "❌ Error testing configuration: " . $e->getMessage() . "\n";
}