// HTTPS Proxy Server | Delulu Fortune Dev
// Proxies HTTPS requests to PHP dev server

const https = require('https');
const http = require('http');
const fs = require('fs');

const SSL_OPTIONS = {
    key: fs.readFileSync('server.key'),
    cert: fs.readFileSync('server.crt')
};

const PHP_HOST = '127.0.0.1';
const PHP_PORT = 8000;
const HTTPS_PORT = 8443;

const server = https.createServer(SSL_OPTIONS, (req, res) => {
    const options = {
        hostname: PHP_HOST,
        port: PHP_PORT,
        path: req.url,
        method: req.method,
        headers: req.headers
    };

    const proxy = http.request(options, (proxyRes) => {
        res.writeHead(proxyRes.statusCode, proxyRes.headers);
        proxyRes.pipe(res);
    });

    proxy.on('error', (err) => {
        console.error('Proxy error:', err.message);
        res.writeHead(502);
        res.end('PHP server not running. Start with: php -S 127.0.0.1:8000');
    });

    req.pipe(proxy);
});

server.listen(HTTPS_PORT, '0.0.0.0', () => {
    console.log(`\n🔒 HTTPS server running at https://localhost:${HTTPS_PORT}`);
    console.log(`   Also accessible at https://192.168.16.150:${HTTPS_PORT} (for mobile)`);
    console.log(`\n📱 For Instagram Web Share to work, open on your PHONE:`);
    console.log(`   https://192.168.16.150:${HTTPS_PORT}`);
    console.log(`   (Accept the self-signed certificate warning)\n`);
    console.log(`⚠️  Make sure PHP server is running: php -S 127.0.0.1:8000\n`);
});
