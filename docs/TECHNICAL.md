# 📖 Technical Documentation

Complete technical reference for Today's Delulu Fortune.

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                        Frontend                              │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │ index.html  │  │  main.css   │  │      app.js         │ │
│  │   (SEO +    │  │(Premium UI) │  │(API calls + cache)  │ │
│  │   Ads)      │  │             │  │                     │ │
│  └─────────────┘  └─────────────┘  └─────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                        API Layer                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │                      api.php                             ││
│  │  • GET /api.php?action=get   → Returns user's fortune   ││
│  │  • GET /api.php?action=share → Tracks share event       ││
│  │  • GET /api.php?action=stats → Returns analytics        ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                      Backend Services                        │
│  ┌───────────────┐  ┌───────────────┐  ┌─────────────────┐ │
│  │   config.php  │  │    db.php     │  │   openai.php    │ │
│  │  (Settings)   │  │   (SQLite)    │  │  (AI Generate)  │ │
│  └───────────────┘  └───────────────┘  └─────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                         Storage                              │
│  ┌─────────────────────────────────────────────────────────┐│
│  │                  data/fortune.db                         ││
│  │  Tables: fortunes, analytics, daily_stats, user_sessions││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

---

## Database Schema

### Tables

#### `fortunes`
Stores generated fortunes (5 per day).

| Column | Type | Description |
|--------|------|-------------|
| id | INTEGER | Primary key |
| fortune_text | TEXT | The fortune content |
| fortune_date | TEXT | Date (YYYY-MM-DD) |
| slot | INTEGER | 1-5, which slot this fortune occupies |
| created_at | DATETIME | When generated |

**Unique constraint**: `(fortune_date, slot)`

#### `analytics`
Raw event log for all interactions.

| Column | Type | Description |
|--------|------|-------------|
| id | INTEGER | Primary key |
| event_type | TEXT | 'view' or 'share' |
| fortune_id | INTEGER | Which fortune |
| visitor_hash | TEXT | Anonymous visitor ID |
| user_agent | TEXT | Browser info |
| referrer | TEXT | Traffic source |
| created_at | DATETIME | Event timestamp |

#### `daily_stats`
Aggregated daily statistics (for performance).

| Column | Type | Description |
|--------|------|-------------|
| stat_date | TEXT | Date (primary key) |
| total_views | INTEGER | Page views |
| unique_visitors | INTEGER | Unique visitors |
| total_shares | INTEGER | Share button clicks |
| updated_at | DATETIME | Last update |

#### `user_sessions`
Tracks which fortune slot each user sees.

| Column | Type | Description |
|--------|------|-------------|
| visitor_hash | TEXT | Anonymous visitor ID (primary key) |
| fortune_slot | INTEGER | Assigned slot 1-5 |
| assigned_at | DATETIME | When assigned |
| expires_at | DATETIME | Session expiry (4-6 hours) |

---

## API Reference

### GET `/api.php?action=get`

Returns the user's assigned fortune for today.

**Response:**
```json
{
  "success": true,
  "fortune": "Someone will think about you… probably.",
  "date": "2024-12-07",
  "slot": 3,
  "cached": true,
  "message": "Your fortune awaits"
}
```

**Flow:**
1. Generate visitor hash from IP + User Agent + Date
2. Check if 5 fortunes exist for today
3. If not, call OpenAI to generate 5 fortunes
4. Get user's assigned slot (or assign new one for 4-6 hours)
5. Return the fortune at that slot
6. Log analytics event

### GET `/api.php?action=share`

Tracks a share event for analytics.

**Response:**
```json
{
  "success": true,
  "message": "Share tracked"
}
```

### GET `/api.php?action=stats&key=admin123`

Returns analytics data (protected endpoint).

**Response:**
```json
{
  "success": true,
  "totals": {
    "views": 12500,
    "visitors": 8200,
    "shares": 1840
  },
  "daily": [
    {
      "stat_date": "2024-12-07",
      "total_views": 450,
      "unique_visitors": 320,
      "total_shares": 65
    }
  ]
}
```

---

## Configuration Reference

### `includes/config.php`

| Constant | Default | Description |
|----------|---------|-------------|
| `OPENAI_API_KEY` | '' | Your OpenAI API key |
| `OPENAI_MODEL` | 'gpt-4o-mini' | Model to use |
| `FORTUNES_PER_DAY` | 5 | How many fortunes per day |
| `SESSION_HOURS_MIN` | 4 | Min hours to show same fortune |
| `SESSION_HOURS_MAX` | 6 | Max hours to show same fortune |
| `CACHE_ENABLED` | true | Enable fortune caching |
| `DEBUG_MODE` | false | Show detailed errors |
| `TRACK_ANALYTICS` | true | Enable analytics |
| `ADS_ENABLED` | true | Enable ad slots |
| `ADSENSE_CLIENT_ID` | '' | Google AdSense ID |
| `SITE_NAME` | "Today's Delulu Fortune" | Site name |
| `SITE_URL` | 'https://delulufortune.com' | Production URL |

---

## Caching Strategy

### Level 1: OpenAI API Cache
- 5 fortunes generated once per day
- Stored in SQLite `fortunes` table
- Only 1 API call per day regardless of traffic

### Level 2: User Session Cache
- Each user assigned 1 of 5 fortunes
- Persists for 4-6 hours (random)
- Stored in SQLite `user_sessions` table

### Level 3: Browser Cache
- API response cached for 5 minutes
- `Cache-Control: private, max-age=300`

### Level 4: Local Storage
- Fortune cached in browser localStorage
- Expires with session (5 hours)
- Prevents unnecessary API calls on refresh

### Level 5: Service Worker
- Static assets cached indefinitely
- Enables offline functionality
- Updates on version change

---

## Security Considerations

### Implemented
- Visitor hashing (IP + UA + Date)
- Input sanitization via PDO prepared statements
- CORS headers configured
- Error messages hidden in production
- Stats endpoint protected with key

### Recommended for Production
- [ ] Use HTTPS only
- [ ] Add rate limiting (100 requests/min/IP)
- [ ] Move admin key to environment variable
- [ ] Add CSRF protection for forms
- [ ] Regular database backups

---

## Performance Optimization

### Current Optimizations
1. **SQLite** - No external DB connection overhead
2. **Single API call** - 5 fortunes in one request
3. **CSS/JS minification** - Reduce file sizes
4. **Preconnect** - Faster Google Fonts loading
5. **Service Worker** - Offline asset caching

### Recommended for Scale
- [ ] Use CDN (Cloudflare) for static assets
- [ ] Enable Gzip/Brotli compression
- [ ] Implement Redis for high-traffic caching
- [ ] Use PHP-FPM with OpCache
- [ ] Database: Switch to MySQL/PostgreSQL at 1M+ requests/day

---

## Deployment Guide

### Option 1: Shared Hosting (Hostinger, GoDaddy)

1. Upload all files via FTP
2. Ensure PHP 7.4+ is enabled
3. Set permissions: `chmod 755 data/`
4. Update `config.php` with production values
5. Point domain to the directory

### Option 2: VPS (DigitalOcean, Linode)

```bash
# Install requirements
sudo apt update
sudo apt install nginx php-fpm php-sqlite3 php-curl

# Clone project
git clone [your-repo] /var/www/delulufortune

# Set permissions
chown -R www-data:www-data /var/www/delulufortune
chmod 755 /var/www/delulufortune/data

# Configure Nginx (see nginx.conf example)
```

### Option 3: Docker

```dockerfile
FROM php:8.1-apache
RUN docker-php-ext-install pdo_sqlite
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/data
```

---

## Troubleshooting

### Fortune not generating
1. Check OpenAI API key is set
2. Check API key has credits
3. Enable `DEBUG_MODE` temporarily
4. Check `data/` directory is writable

### Database errors
1. Delete `data/fortune.db` to reset
2. Ensure PHP SQLite extension is enabled
3. Check directory permissions

### Styles not loading
1. Verify file paths are correct
2. Check for 404 errors in browser console
3. Clear browser cache

### API returning errors
1. Check PHP error logs
2. Enable `DEBUG_MODE` for details
3. Test with: `curl http://localhost:8000/api.php?action=get`

---

## File Reference

```
├── index.html          # Main page with SEO + ads
├── api.php             # API endpoint
├── manifest.json       # PWA manifest
├── sw.js               # Service worker
├── assets/
│   ├── css/
│   │   └── main.css    # All styles
│   └── images/         # Icons, OG image
├── scripts/
│   └── app.js          # Client logic
├── includes/
│   ├── config.php      # Configuration
│   ├── db.php          # SQLite handler
│   └── openai.php      # AI integration
├── data/
│   └── fortune.db      # SQLite database
└── docs/
    ├── LAUNCH_ROADMAP.md
    └── TECHNICAL.md
```

---

*Last updated: December 2024*
