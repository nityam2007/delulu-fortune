/**
 * App.js | Today's Delulu Fortune
 * Client-side logic with caching and analytics
 */

let currentFortune = '';
let fortuneSlot = 0;

// Gen Z chaos data
const VIBE_TAGS = [
    "main character energy",
    "NPC mode activated",
    "villain origin story loading",
    "chronically online but thriving",
    "situationship survivor",
    "healing from a spotify playlist",
    "touch grass era",
    "hot girl summer (winter edition)",
    "romanticizing everything era",
    "delulu is the solulu",
    "unhinged but make it cute",
    "red flag collector",
    "green flag only zone",
    "trauma response but aesthetic",
    "feral mode: ON"
];

const LUCKY_ITEMS = [
    "biryani", "maggi at 2am", "perfect chai", "vada pav", "samosa",
    "window seat", "green signal streak", "AC working", "phone at 100%",
    "free wifi", "no traffic", "extra fries", "refund email", "surprise holiday",
    "perfect photo first try", "song recommendation that hits", "finding cash in jeans"
];

const AVOID_ITEMS = [
    "zoom calls", "ex's story", "\"we need to talk\" texts", "family WhatsApp group",
    "autorickshaw in rain", "Monday motivation posts", "cold coffee", "low battery",
    "parking hunt", "\"shaadi kab?\" aunties", "reply all emails", "3am thoughts",
    "group project", "read receipts", "alarm snooze", "spicy food before meeting"
];

const SCREENSHOT_PROMPTS = [
    "screenshot energy is strong today",
    "this needs to be on your story tbh",
    "send this to someone who needs it",
    "save this for later (you won't)",
    "screenshot now, think later",
    "your sign to share this rn",
    "main character moment captured"
];

// Simple seeded random
function seededRandom(seed) {
    const x = Math.sin(seed) * 10000;
    return x - Math.floor(x);
}

// Get hash for user consistency
function getUserSeed() {
    const today = new Date().toISOString().split('T')[0];
    let hash = 0;
    const str = today + (localStorage.getItem('deluluSeed') || Math.random().toString());
    for (let i = 0; i < str.length; i++) {
        hash = ((hash << 5) - hash) + str.charCodeAt(i);
        hash |= 0;
    }
    if (!localStorage.getItem('deluluSeed')) {
        localStorage.setItem('deluluSeed', hash.toString());
    }
    return Math.abs(hash);
}

// Generate delulu stats
function generateStats() {
    const seed = getUserSeed();

    // Aura: -100 to +1000 (weighted towards positive for comedy)
    const auraBase = Math.floor(seededRandom(seed * 1) * 1100) - 100;
    const aura = auraBase > 0 ? `+${auraBase}` : auraBase.toString();

    // Delulu level: 60-99% (always high for comedy)
    const deluluLevel = 60 + Math.floor(seededRandom(seed * 2) * 40);

    // Random picks from arrays
    const vibeIndex = Math.floor(seededRandom(seed * 3) * VIBE_TAGS.length);
    const luckyIndex = Math.floor(seededRandom(seed * 4) * LUCKY_ITEMS.length);
    const avoidIndex = Math.floor(seededRandom(seed * 5) * AVOID_ITEMS.length);
    const promptIndex = Math.floor(seededRandom(seed * 6) * SCREENSHOT_PROMPTS.length);

    return {
        aura,
        deluluLevel: `${deluluLevel}%`,
        vibe: VIBE_TAGS[vibeIndex],
        lucky: LUCKY_ITEMS[luckyIndex],
        avoid: AVOID_ITEMS[avoidIndex],
        prompt: SCREENSHOT_PROMPTS[promptIndex]
    };
}

// Confetti burst
function launchConfetti() {
    const container = document.getElementById('confettiContainer');
    if (!container) return;

    const colors = ['#ff6b35', '#ffd23f', '#ff8a5c', '#c026d3', '#22d3ee', '#34d399'];
    const confettiCount = 50;

    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = `${Math.random() * 100}%`;
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.animationDelay = `${Math.random() * 0.5}s`;
        confetti.style.animationDuration = `${1 + Math.random() * 1}s`;
        container.appendChild(confetti);
    }

    // Cleanup after animation
    setTimeout(() => {
        container.innerHTML = '';
    }, 2500);
}

// Show delulu stats
function showDeluluStats() {
    const stats = generateStats();
    const statsPanel = document.getElementById('deluluStats');

    document.getElementById('auraValue').textContent = stats.aura;
    document.getElementById('deluluValue').textContent = stats.deluluLevel;
    document.getElementById('vibeTag').textContent = stats.vibe;
    document.getElementById('luckyItem').textContent = stats.lucky;
    document.getElementById('avoidItem').textContent = stats.avoid;
    document.getElementById('screenshotPrompt').textContent = stats.prompt;

    statsPanel?.classList.add('visible');
}

document.addEventListener('DOMContentLoaded', () => {
    initParticles();
    initEventListeners();
    checkLocalCache();
});

/**
 * Initialize floating particles
 */
function initParticles() {
    const container = document.getElementById('particles');
    if (!container) return;

    const particleCount = window.innerWidth < 600 ? 12 : 25;
    const colors = ['#7c3aed', '#c026d3', '#f472b6', '#22d3ee', '#34d399'];

    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = `${Math.random() * 100}%`;
        particle.style.animationDelay = `${Math.random() * 10}s`;
        particle.style.animationDuration = `${8 + Math.random() * 6}s`;

        const color = colors[Math.floor(Math.random() * colors.length)];
        particle.style.background = color;
        particle.style.boxShadow = `0 0 8px ${color}`;

        container.appendChild(particle);
    }
}

/**
 * Initialize event listeners
 */
function initEventListeners() {
    const revealBtn = document.getElementById('revealBtn');
    const crystalBall = document.getElementById('crystalBall');
    const shareTwitter = document.getElementById('shareTwitter');
    const shareWhatsapp = document.getElementById('shareWhatsapp');
    const shareInstagram = document.getElementById('shareInstagram');
    const shareCopy = document.getElementById('shareCopy');
    const logoIcon = document.getElementById('logoIcon');

    revealBtn?.addEventListener('click', revealFortune);
    crystalBall?.addEventListener('click', revealFortune);

    shareTwitter?.addEventListener('click', shareToTwitter);
    shareWhatsapp?.addEventListener('click', shareToWhatsapp);
    shareInstagram?.addEventListener('click', shareToInstagram);
    shareCopy?.addEventListener('click', copyToClipboard);

    logoIcon?.addEventListener('click', changeThemeColor);
}

/**
 * Check local cache before fetching
 */
function checkLocalCache() {
    const cached = localStorage.getItem('deluluFortune');
    if (cached) {
        try {
            const data = JSON.parse(cached);
            const now = Date.now();

            if (data.expires > now && data.date === getTodayDate()) {
                currentFortune = data.fortune;
                fortuneSlot = data.slot;
                return;
            }
        } catch (e) {
            localStorage.removeItem('deluluFortune');
        }
    }
}

/**
 * Get today's date string
 */
function getTodayDate() {
    return new Date().toISOString().split('T')[0];
}

/**
 * Fetch and reveal the fortune
 */
async function revealFortune() {
    const revealBtn = document.getElementById('revealBtn');
    const crystalBall = document.getElementById('crystalBall');
    const fortuneCard = document.getElementById('fortuneCard');
    const fortuneText = document.getElementById('fortuneText');
    const cosmicTag = document.getElementById('cosmicTag');

    // Check cache first
    if (currentFortune) {
        showFortune(currentFortune, getTodayDate(), fortuneSlot, true);
        return;
    }

    revealBtn?.classList.add('hidden');
    crystalBall?.classList.add('hidden');
    fortuneCard?.classList.add('revealed');

    if (fortuneText) {
        fortuneText.innerHTML = '<span class="loading-text">Consulting the cosmos...</span>';
    }

    try {
        const response = await fetch('api.php?action=get');
        const data = await response.json();

        if (data.success) {
            currentFortune = data.fortune;
            fortuneSlot = data.slot || 1;

            const cacheData = {
                fortune: data.fortune,
                slot: fortuneSlot,
                date: data.date,
                expires: Date.now() + (5 * 60 * 60 * 1000)
            };
            localStorage.setItem('deluluFortune', JSON.stringify(cacheData));

            await new Promise(resolve => setTimeout(resolve, 1000));

            showFortune(data.fortune, data.date, fortuneSlot, data.cached);
        } else {
            throw new Error(data.error || 'Failed to get fortune');
        }
    } catch (error) {
        console.error('Error fetching fortune:', error);
        if (fortuneText) {
            fortuneText.textContent = 'The stars are shy today... try refreshing!';
        }
        if (cosmicTag) {
            cosmicTag.innerHTML = '<svg class="tag-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg> Connection lost';
        }

        setTimeout(() => {
            revealBtn?.classList.remove('hidden');
            crystalBall?.classList.remove('hidden');
            fortuneCard?.classList.remove('revealed');
        }, 2500);
    }
}

/**
 * Display the fortune
 */
function showFortune(fortune, date, slot, cached) {
    const revealBtn = document.getElementById('revealBtn');
    const crystalBall = document.getElementById('crystalBall');
    const fortuneCard = document.getElementById('fortuneCard');
    const fortuneText = document.getElementById('fortuneText');
    const fortuneDate = document.getElementById('fortuneDate');
    const cosmicTag = document.getElementById('cosmicTag');
    const shareSection = document.getElementById('shareSection');

    revealBtn?.classList.add('hidden');
    crystalBall?.classList.add('hidden');
    fortuneCard?.classList.add('revealed');

    if (fortuneText) fortuneText.textContent = `"${fortune}"`;
    if (fortuneDate) fortuneDate.textContent = formatDate(date);

    if (cosmicTag) {
        const icon = cached
            ? '<svg class="tag-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>'
            : '<svg class="tag-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0L14.5 9.5L24 12L14.5 14.5L12 24L9.5 14.5L0 12L9.5 9.5L12 0Z"/></svg>';
        const text = cached ? 'Your personal fortune' : 'Fresh from the cosmos';
        cosmicTag.innerHTML = `${icon} ${text}`;
    }

    fortuneCard?.classList.add('animate-reveal');

    // Launch confetti!
    launchConfetti();

    setTimeout(() => {
        shareSection?.classList.add('visible');
        // Show the delulu stats after fortune
        showDeluluStats();
    }, 400);
}

/**
 * Format date nicely
 */
function formatDate(dateStr) {
    const date = new Date(dateStr);
    const options = { weekday: 'long', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

/**
 * Share to Twitter/X
 */
function shareToTwitter() {
    const text = encodeURIComponent(`Today's Delulu Fortune:\n\n"${currentFortune}"\n\nGet yours:`);
    const url = encodeURIComponent(window.location.origin);
    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank', 'width=550,height=420');
    trackShare();
}

/**
 * Share to WhatsApp
 */
function shareToWhatsapp() {
    const text = encodeURIComponent(`*Today's Delulu Fortune*\n\n"${currentFortune}"\n\nGet your daily delulu: ${window.location.origin}`);
    window.open(`https://wa.me/?text=${text}`, '_blank');
    trackShare();
}

/**
 * Share to Instagram (copy for stories)
 */
function shareToInstagram() {
    const text = `Today's Delulu Fortune\n\n"${currentFortune}"\n\ndelulufortune.com`;
    copyText(text);
    showToast('Copied! Now paste in your Instagram story');
    trackShare();
}

/**
 * Copy to clipboard
 */
async function copyToClipboard() {
    const textToCopy = `Today's Delulu Fortune\n\n"${currentFortune}"\n\nGet yours: ${window.location.origin}`;
    await copyText(textToCopy);
    showToast('Copied to clipboard!');
    trackShare();
}

/**
 * Copy text helper
 */
async function copyText(text) {
    try {
        await navigator.clipboard.writeText(text);
    } catch (err) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }
}

/**
 * Show toast notification
 */
function showToast(message) {
    const toast = document.getElementById('copyToast');
    if (toast) {
        toast.textContent = message;
        toast.classList.add('visible');
        setTimeout(() => toast.classList.remove('visible'), 2500);
    }
}

/**
 * Track share for analytics
 */
async function trackShare() {
    try {
        await fetch('api.php?action=share');
    } catch (e) { }
}

/**
 * Easter egg - change theme colors
 */
function changeThemeColor() {
    const hue1 = Math.floor(Math.random() * 360);
    const hue2 = (hue1 + 40) % 360;
    const hue3 = (hue1 + 80) % 360;

    document.body.style.setProperty('--accent-violet', `hsl(${hue1}, 70%, 55%)`);
    document.body.style.setProperty('--accent-fuchsia', `hsl(${hue2}, 70%, 55%)`);
    document.body.style.setProperty('--accent-rose', `hsl(${hue3}, 70%, 65%)`);
}

// Service Worker registration for PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js').catch(() => { });
    });
}
