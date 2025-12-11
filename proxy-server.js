const http = require('http');
const https = require('https');

const PORT = 3000;
const BACKEND_URL = 'http://127.0.0.1:8000';

const server = http.createServer((req, res) => {
  // CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
  res.setHeader('Access-Control-Allow-Credentials', 'true');

  if (req.method === 'OPTIONS') {
    res.writeHead(200);
    res.end();
    return;
  }

  // Build the target URL
  let path = req.url;
  if (path.startsWith('/api/')) {
    path = '/api/v1' + path.substring(4);
  }
  
  const targetUrl = BACKEND_URL + path;
  console.log(`[Proxy] ${req.method} ${req.url} -> ${targetUrl}`);

  // Parse the target URL
  const urlObj = new URL(targetUrl);
  const protocol = urlObj.protocol === 'https:' ? https : http;

  // Prepare options for the proxy request
  const options = {
    method: req.method,
    headers: {
      ...req.headers,
      host: urlObj.host,
    },
  };

  // Remove content-length if present (let Node handle it)
  delete options.headers['content-length'];

  // Make the proxy request
  const proxyReq = protocol.request(urlObj, options, (proxyRes) => {
    // Copy response status and headers
    res.writeHead(proxyRes.statusCode, proxyRes.headers);
    proxyRes.pipe(res);
  });

  proxyReq.on('error', (error) => {
    console.error(`[Proxy Error] ${error.message}`);
    res.writeHead(502, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ 
      success: false, 
      error: `Proxy error: ${error.message}` 
    }));
  });

  // Pipe request body if present
  if (req.method !== 'GET' && req.method !== 'HEAD') {
    req.pipe(proxyReq);
  } else {
    proxyReq.end();
  }
});

server.listen(PORT, '0.0.0.0', () => {
  console.log(`🚀 API Proxy Server running on http://0.0.0.0:${PORT}`);
  console.log(`📡 Backend: ${BACKEND_URL}`);
  console.log(`📱 Access from mobile: http://192.168.0.153:${PORT}`);
});

server.on('error', (error) => {
  if (error.code === 'EADDRINUSE') {
    console.error(`❌ Port ${PORT} is already in use. Try changing the PORT variable.`);
  } else {
    console.error('Server error:', error);
  }
  process.exit(1);
});
