#!/usr/bin/env node

import http from 'node:http';
import { createReadStream, existsSync, statSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const staticRoot = process.env.STATIC_ROOT
  ? path.resolve(process.env.STATIC_ROOT)
  : path.resolve(__dirname, '..', 'out');
const port = Number(process.env.PORT || '8080');

const MIME_TYPES = {
  '.css': 'text/css; charset=utf-8',
  '.gif': 'image/gif',
  '.html': 'text/html; charset=utf-8',
  '.ico': 'image/x-icon',
  '.jpeg': 'image/jpeg',
  '.jpg': 'image/jpeg',
  '.js': 'application/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.map': 'application/json; charset=utf-8',
  '.mp4': 'video/mp4',
  '.pdf': 'application/pdf',
  '.png': 'image/png',
  '.svg': 'image/svg+xml',
  '.txt': 'text/plain; charset=utf-8',
  '.webm': 'video/webm',
  '.woff': 'font/woff',
  '.woff2': 'font/woff2',
  '.xml': 'application/xml; charset=utf-8',
};

function safeResolve(urlPath) {
  const normalizedPath = path.normalize(decodeURIComponent(urlPath)).replace(/^(\.\.(\/|\\|$))+/, '');
  const resolvedPath = path.resolve(staticRoot, `.${normalizedPath}`);

  if (!resolvedPath.startsWith(staticRoot)) {
    return null;
  }

  return resolvedPath;
}

function getCandidateFiles(urlPath) {
  const resolvedPath = safeResolve(urlPath);
  if (!resolvedPath) {
    return [];
  }

  const candidates = [];

  if (path.extname(resolvedPath)) {
    candidates.push(resolvedPath);
  } else {
    candidates.push(path.join(resolvedPath, 'index.html'));
    candidates.push(`${resolvedPath}.html`);
  }

  return candidates;
}

function serveFile(filePath, statusCode, response) {
  const extension = path.extname(filePath).toLowerCase();
  response.writeHead(statusCode, {
    'Content-Type': MIME_TYPES[extension] || 'application/octet-stream',
    'Cache-Control': extension === '.html' ? 'no-cache' : 'public, max-age=31536000, immutable',
  });
  createReadStream(filePath).pipe(response);
}

const server = http.createServer((request, response) => {
  const pathname = new URL(request.url || '/', 'http://localhost').pathname;
  const candidates = getCandidateFiles(pathname);

  for (const candidate of candidates) {
    if (existsSync(candidate) && statSync(candidate).isFile()) {
      serveFile(candidate, 200, response);
      return;
    }
  }

  const notFoundPage = path.join(staticRoot, '404.html');
  if (existsSync(notFoundPage)) {
    serveFile(notFoundPage, 404, response);
    return;
  }

  response.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
  response.end('Not Found');
});

server.listen(port, '0.0.0.0', () => {
  console.log(`Serving static site from ${staticRoot} on port ${port}`);
});
