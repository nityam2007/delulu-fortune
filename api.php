<?php
// API Endpoint | Today's Delulu Fortune

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: private, max-age=300'); // 5 min browser cache

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/openai.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Generate visitor hash for session persistence
function getVisitorHash() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    return hash('sha256', $ip . $ua . date('Y-m-d'));
}

try {
    $action = $_GET['action'] ?? 'get';
    
    switch ($action) {
        case 'get':
            getFortune();
            break;
        case 'share':
            trackShare();
            break;
        case 'stats':
            getStats();
            break;
        default:
            jsonResponse(['error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    $message = DEBUG_MODE ? $e->getMessage() : 'Something went wrong. The universe is recalibrating...';
    jsonResponse(['error' => $message], 500);
}

function getFortune() {
    $db = Database::getInstance();
    $visitorHash = getVisitorHash();
    
    // Check if we have today's fortunes
    $fortuneCount = $db->getTodaysFortuneCount();
    
    if ($fortuneCount < FORTUNES_PER_DAY) {
        // Generate new fortunes for today
        $openai = new OpenAI();
        $fortunes = $openai->generateFortunes(FORTUNES_PER_DAY);
        
        foreach ($fortunes as $slot => $text) {
            $db->saveFortune($text, $slot + 1);
        }
    }
    
    // Get user's assigned fortune slot (persists 4-6 hours)
    $slot = $db->getUserFortuneSlot($visitorHash);
    $fortune = $db->getFortuneBySlot($slot);
    
    if (!$fortune) {
        // Fallback to slot 1
        $fortune = $db->getFortuneBySlot(1);
    }
    
    // Log analytics
    if (TRACK_ANALYTICS) {
        $db->logEvent(
            'view',
            $fortune['id'] ?? null,
            $visitorHash,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $_SERVER['HTTP_REFERER'] ?? null
        );
    }
    
    jsonResponse([
        'success' => true,
        'fortune' => $fortune['fortune_text'],
        'date' => $fortune['fortune_date'],
        'slot' => $slot,
        'cached' => true,
        'message' => 'Your fortune awaits'
    ]);
}

function trackShare() {
    $db = Database::getInstance();
    $visitorHash = getVisitorHash();
    $slot = $db->getUserFortuneSlot($visitorHash);
    $fortune = $db->getFortuneBySlot($slot);
    
    if ($fortune && TRACK_ANALYTICS) {
        $db->logEvent('share', $fortune['id'], $visitorHash);
        jsonResponse(['success' => true, 'message' => 'Share tracked']);
    } else {
        jsonResponse(['error' => 'No fortune to track'], 404);
    }
}

function getStats() {
    // Only allow in debug mode or with secret key
    $key = $_GET['key'] ?? '';
    if (!DEBUG_MODE && $key !== ADMIN_KEY) {
        jsonResponse(['error' => 'Unauthorized'], 403);
    }
    
    $db = Database::getInstance();
    $summary = $db->getAnalyticsSummary(30);
    $totals = $db->getTotalStats();
    
    jsonResponse([
        'success' => true,
        'totals' => $totals,
        'daily' => $summary
    ]);
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}
