<?php
// Database Handler (SQLite) | Today's Delulu Fortune

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            if (!is_dir(DATA_PATH)) {
                mkdir(DATA_PATH, 0755, true);
            }
            
            $this->pdo = new PDO('sqlite:' . DB_FILE);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            $this->initTables();
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Database error: " . $e->getMessage());
            }
            die("Database connection failed.");
        }
    }
    
    private function initTables() {
        // Fortunes table - stores 5 per day
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS fortunes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fortune_text TEXT NOT NULL,
                fortune_date TEXT NOT NULL,
                slot INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(fortune_date, slot)
            )
        ");
        
        // Analytics table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS analytics (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                event_type TEXT NOT NULL,
                fortune_id INTEGER,
                visitor_hash TEXT,
                user_agent TEXT,
                referrer TEXT,
                country TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Daily stats cache
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS daily_stats (
                stat_date TEXT PRIMARY KEY,
                total_views INTEGER DEFAULT 0,
                unique_visitors INTEGER DEFAULT 0,
                total_shares INTEGER DEFAULT 0,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // User sessions for fortune persistence
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS user_sessions (
                visitor_hash TEXT PRIMARY KEY,
                fortune_slot INTEGER NOT NULL,
                assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME NOT NULL
            )
        ");
        
        // Create indexes
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_fortune_date ON fortunes(fortune_date)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_analytics_date ON analytics(created_at)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_session_expires ON user_sessions(expires_at)");
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // Get today's fortunes count
    public function getTodaysFortuneCount() {
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM fortunes WHERE fortune_date = ?");
        $stmt->execute([$today]);
        return (int)$stmt->fetch()['count'];
    }
    
    // Get all today's fortunes
    public function getTodaysFortunes() {
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT * FROM fortunes WHERE fortune_date = ? ORDER BY slot");
        $stmt->execute([$today]);
        return $stmt->fetchAll();
    }
    
    // Get specific fortune by slot
    public function getFortuneBySlot($slot) {
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT * FROM fortunes WHERE fortune_date = ? AND slot = ?");
        $stmt->execute([$today, $slot]);
        return $stmt->fetch();
    }
    
    // Save fortune with slot
    public function saveFortune($fortuneText, $slot) {
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("INSERT OR REPLACE INTO fortunes (fortune_text, fortune_date, slot) VALUES (?, ?, ?)");
        $stmt->execute([$fortuneText, $today, $slot]);
        return $this->pdo->lastInsertId();
    }
    
    // Get or assign user fortune slot (persists 4-6 hours)
    public function getUserFortuneSlot($visitorHash) {
        // Clean expired sessions
        $this->pdo->exec("DELETE FROM user_sessions WHERE expires_at < datetime('now')");
        
        // Check existing session
        $stmt = $this->pdo->prepare("SELECT fortune_slot FROM user_sessions WHERE visitor_hash = ? AND expires_at > datetime('now')");
        $stmt->execute([$visitorHash]);
        $session = $stmt->fetch();
        
        if ($session) {
            return (int)$session['fortune_slot'];
        }
        
        // Assign new random slot (1-5) with 4-6 hour expiry
        $slot = rand(1, FORTUNES_PER_DAY);
        $hours = rand(4, 6);
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$hours} hours"));
        
        $stmt = $this->pdo->prepare("INSERT OR REPLACE INTO user_sessions (visitor_hash, fortune_slot, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$visitorHash, $slot, $expiresAt]);
        
        return $slot;
    }
    
    // Analytics: Log event
    public function logEvent($eventType, $fortuneId = null, $visitorHash = null, $userAgent = null, $referrer = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO analytics (event_type, fortune_id, visitor_hash, user_agent, referrer) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$eventType, $fortuneId, $visitorHash, $userAgent, $referrer]);
        
        // Update daily stats
        $this->updateDailyStats($eventType, $visitorHash);
    }
    
    private function updateDailyStats($eventType, $visitorHash) {
        $today = date('Y-m-d');
        
        // Ensure row exists
        $this->pdo->prepare("INSERT OR IGNORE INTO daily_stats (stat_date) VALUES (?)")->execute([$today]);
        
        if ($eventType === 'view') {
            $this->pdo->prepare("UPDATE daily_stats SET total_views = total_views + 1, updated_at = datetime('now') WHERE stat_date = ?")->execute([$today]);
            
            // Check if unique visitor today
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as c FROM analytics WHERE visitor_hash = ? AND event_type = 'view' AND date(created_at) = ?");
            $stmt->execute([$visitorHash, $today]);
            if ($stmt->fetch()['c'] <= 1) {
                $this->pdo->prepare("UPDATE daily_stats SET unique_visitors = unique_visitors + 1 WHERE stat_date = ?")->execute([$today]);
            }
        } elseif ($eventType === 'share') {
            $this->pdo->prepare("UPDATE daily_stats SET total_shares = total_shares + 1, updated_at = datetime('now') WHERE stat_date = ?")->execute([$today]);
        }
    }
    
    // Get analytics summary
    public function getAnalyticsSummary($days = 30) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM daily_stats 
            WHERE stat_date >= date('now', '-' || ? || ' days')
            ORDER BY stat_date DESC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
    
    // Get total stats
    public function getTotalStats() {
        $stmt = $this->pdo->query("
            SELECT 
                SUM(total_views) as views,
                SUM(unique_visitors) as visitors,
                SUM(total_shares) as shares
            FROM daily_stats
        ");
        return $stmt->fetch();
    }
}
