<?php
// Configuration | delulu fortune

date_default_timezone_set('Asia/Kolkata');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('DATA_PATH', ROOT_PATH . '/data');
define('DB_FILE', DATA_PATH . '/fortune.db');

// ============================================
// UPDATE THESE BEFORE DEPLOYMENT
// ============================================

define('OPENAI_API_KEY', 'YOUR_OPENAI_API_KEY_HERE');  // <-- ADD KEY
define('OPENAI_MODEL', 'gpt-4o-mini');

// Fortune Settings
define('FORTUNES_PER_DAY', 5);
define('SESSION_HOURS_MIN', 4);
define('SESSION_HOURS_MAX', 6);

// Cache
define('CACHE_ENABLED', true);
define('CACHE_FORTUNE_HOURS', 24);

// App
define('DEBUG_MODE', false);
define('SITE_NAME', 'delulu fortune');
define('SITE_URL', 'https://delulu.nytm.in');
define('SITE_DESCRIPTION', 'get your daily delulu fortune');

// Analytics & Ads
define('TRACK_ANALYTICS', true);
define('ADS_ENABLED', true);
define('ADSENSE_CLIENT_ID', '');  // <-- Add when approved

// Admin stats key
define('ADMIN_KEY', 'changeme123');  // <-- CHANGE THIS

// Errors
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
