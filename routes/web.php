<?php

declare(strict_types=1);

use Accelio\Core\Database;
use Accelio\Http\Request;
use Accelio\Http\Response;

// Serve the Svelte SPA for the root route
$router->get('/', function (Request $request) {
    $buildIndexPath = __DIR__ . '/../public/build/index.html';
    
    if (file_exists($buildIndexPath)) {
        $html = file_get_contents($buildIndexPath);
        return Response::html($html);
    }
    
    // Fallback: redirect to Vite dev server during development
    return Response::html(
        '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Windows XP</title></head>' .
        '<body><h2>Build not found</h2><p>Run <code>cd frontend && npm run build</code> first, ' .
        'or use <code>npm run dev</code> at <a href="http://localhost:5173">localhost:5173</a></p></body></html>'
    );
});

// ──────────────────────────────────────────────
// Documents API
// ──────────────────────────────────────────────

// List all documents
$router->get('/api/documents', function (Request $request) {
    $db = Database::connection();
    $stmt = $db->query('SELECT id, name, type, created_at, updated_at FROM documents ORDER BY updated_at DESC');
    return Response::json(['ok' => true, 'data' => $stmt->fetchAll()]);
});

// Get single document
$router->get('/api/documents/{id}', function (Request $request, string $id) {
    $db = Database::connection();
    $stmt = $db->prepare('SELECT * FROM documents WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $doc = $stmt->fetch();

    if (!$doc) {
        return Response::json(['ok' => false, 'error' => 'Document not found'], 404);
    }

    return Response::json(['ok' => true, 'data' => $doc]);
});

// Create document
$router->post('/api/documents', function (Request $request) {
    $name = $request->input('name', 'Untitled.txt');
    $type = $request->input('type', 'text');
    $content = $request->input('content', '');

    $db = Database::connection();
    $stmt = $db->prepare('INSERT INTO documents (name, type, content) VALUES (:name, :type, :content)');
    $stmt->execute([
        'name' => $name,
        'type' => $type,
        'content' => $content,
    ]);

    $id = $db->lastInsertId();

    return Response::json(['ok' => true, 'data' => ['id' => (int) $id, 'name' => $name, 'type' => $type]], 201);
});

// Update document
$router->put('/api/documents/{id}', function (Request $request, string $id) {
    $db = Database::connection();

    // Check existence
    $stmt = $db->prepare('SELECT * FROM documents WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $doc = $stmt->fetch();

    if (!$doc) {
        return Response::json(['ok' => false, 'error' => 'Document not found'], 404);
    }

    $name = $request->input('name', $doc['name']);
    $content = $request->input('content', $doc['content']);

    $stmt = $db->prepare("UPDATE documents SET name = :name, content = :content, updated_at = datetime('now') WHERE id = :id");
    $stmt->execute([
        'name' => $name,
        'content' => $content,
        'id' => $id,
    ]);

    return Response::json(['ok' => true, 'data' => ['id' => (int) $id, 'name' => $name]]);
});

// Delete document
$router->delete('/api/documents/{id}', function (Request $request, string $id) {
    $db = Database::connection();

    $stmt = $db->prepare('SELECT * FROM documents WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $doc = $stmt->fetch();

    if (!$doc) {
        return Response::json(['ok' => false, 'error' => 'Document not found'], 404);
    }

    // If it's an image, delete the file too
    if ($doc['file_path'] && file_exists(__DIR__ . '/../storage/' . $doc['file_path'])) {
        unlink(__DIR__ . '/../storage/' . $doc['file_path']);
    }

    $stmt = $db->prepare('DELETE FROM documents WHERE id = :id');
    $stmt->execute(['id' => $id]);

    return Response::json(['ok' => true]);
});

// Upload image (Paint save)
$router->post('/api/documents/upload', function (Request $request) {
    $name = $request->input('name', 'Untitled.png');
    $imageData = $request->input('image', '');

    if (empty($imageData)) {
        return Response::json(['ok' => false, 'error' => 'No image data provided'], 400);
    }

    // Decode base64 image
    $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
    $decoded = base64_decode($base64, true);

    if ($decoded === false) {
        return Response::json(['ok' => false, 'error' => 'Invalid base64 image data'], 400);
    }

    // Save to storage/images/
    $imagesDir = __DIR__ . '/../storage/images';
    if (!is_dir($imagesDir)) {
        mkdir($imagesDir, 0755, true);
    }

    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $name);
    if (!str_ends_with(strtolower($filename), '.png')) {
        $filename .= '.png';
    }

    file_put_contents($imagesDir . '/' . $filename, $decoded);

    // Check if we're updating an existing document
    $docId = $request->input('document_id');
    $db = Database::connection();

    if ($docId) {
        $stmt = $db->prepare('SELECT * FROM documents WHERE id = :id');
        $stmt->execute(['id' => $docId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Delete old file if exists
            if ($existing['file_path'] && file_exists(__DIR__ . '/../storage/' . $existing['file_path'])) {
                unlink(__DIR__ . '/../storage/' . $existing['file_path']);
            }

            $stmt = $db->prepare("UPDATE documents SET name = :name, file_path = :path, updated_at = datetime('now') WHERE id = :id");
            $stmt->execute([
                'name' => $name,
                'path' => 'images/' . $filename,
                'id' => $docId,
            ]);

            return Response::json(['ok' => true, 'data' => ['id' => (int) $docId, 'name' => $name]]);
        }
    }

    // Create new document
    $stmt = $db->prepare('INSERT INTO documents (name, type, file_path) VALUES (:name, :type, :path)');
    $stmt->execute([
        'name' => $name,
        'type' => 'image',
        'path' => 'images/' . $filename,
    ]);

    $id = $db->lastInsertId();
    return Response::json(['ok' => true, 'data' => ['id' => (int) $id, 'name' => $name]], 201);
});

// Serve saved image
$router->get('/api/documents/{id}/image', function (Request $request, string $id) {
    $db = Database::connection();
    $stmt = $db->prepare('SELECT * FROM documents WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $doc = $stmt->fetch();

    if (!$doc || !$doc['file_path']) {
        return Response::json(['ok' => false, 'error' => 'Image not found'], 404);
    }

    $filePath = __DIR__ . '/../storage/' . $doc['file_path'];
    if (!file_exists($filePath)) {
        return Response::json(['ok' => false, 'error' => 'Image file missing'], 404);
    }

    $imageData = file_get_contents($filePath);
    return new Response(
        content: $imageData,
        status: 200,
        headers: [
            'Content-Type' => 'image/png',
            'Content-Length' => (string) strlen($imageData),
            'Cache-Control' => 'public, max-age=3600',
        ],
    );
});

// ──────────────────────────────────────────────
// Web Proxy (for Internet Explorer)
// ──────────────────────────────────────────────

$router->get('/api/proxy', function (Request $request) {
    $targetUrl = $request->query('url', '');

    if (empty($targetUrl)) {
        return Response::html('<html><body><h1>No URL provided</h1><p>Enter a URL in the address bar above.</p></body></html>');
    }

    // Only allow http/https
    $scheme = parse_url($targetUrl, PHP_URL_SCHEME);
    if (!in_array($scheme, ['http', 'https'], true)) {
        return Response::html('<html><body><h1>Invalid URL</h1><p>Only HTTP and HTTPS URLs are supported.</p></body></html>');
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:52.0) Gecko/20100101 Firefox/52.0\r\nAccept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n",
            'timeout' => 10,
            'follow_location' => true,
            'max_redirects' => 5,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    $html = @file_get_contents($targetUrl, false, $context);

    if ($html === false) {
        return Response::html(
            '<html><body style="font-family: Tahoma, sans-serif; padding: 40px;">' .
            '<h1>The page cannot be displayed</h1>' .
            '<p>The page you are looking for is currently unavailable.</p>' .
            '<hr><p>Internet Explorer</p></body></html>'
        );
    }

    // Determine the base URL for rewriting relative paths
    $parsed = parse_url($targetUrl);
    $baseOrigin = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');
    $pathDir = str_replace('\\', '/', dirname($parsed['path'] ?? '/'));
    $basePath = $baseOrigin . rtrim($pathDir, '/') . '/';

    // 1. Convert ALL protocol-relative URLs (//domain.com/...) to https://
    //    This covers src, href, srcset, inline styles, etc.
    //    Pattern: quote or whitespace followed by //letter (avoids matching http:// itself)
    $html = preg_replace('/(["\',\s])\/\/([a-zA-Z])/', '$1https://$2', $html);

    // 2. Remove any existing <base> tags
    $html = preg_replace('/<base[^>]*>/i', '', $html);

    // 3. Inject our <base> tag so relative URLs for images/CSS/JS resolve correctly
    if (stripos($html, '<head') !== false) {
        $html = preg_replace(
            '/(<head[^>]*>)/i',
            '$1<base href="' . htmlspecialchars($baseOrigin . ($parsed['path'] ?? '/')) . '" target="_self">',
            $html,
            1
        );
    } else {
        $html = '<base href="' . htmlspecialchars($basePath) . '" target="_self">' . $html;
    }

    // 4. Rewrite <a href="..."> to go through proxy
    $proxyBase = '/api/proxy?url=';

    $makeAbsolute = function (string $url) use ($baseOrigin, $basePath): string {
        if (str_starts_with($url, 'https://') || str_starts_with($url, 'http://')) {
            return $url;
        }
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }
        if (str_starts_with($url, '/')) {
            return $baseOrigin . $url;
        }
        return $basePath . $url;
    };

    $html = preg_replace_callback(
        '/(<a\s[^>]*href=["\'])([^"\']*?)(["\'])/i',
        function ($matches) use ($proxyBase, $makeAbsolute) {
            $href = $matches[2];
            // Skip anchors, javascript:, mailto:, data:, empty
            if (empty($href) || $href[0] === '#' || preg_match('/^(javascript:|mailto:|data:|tel:)/i', $href)) {
                return $matches[0];
            }
            // Skip if already proxied
            if (str_contains($href, '/api/proxy')) {
                return $matches[0];
            }
            $absolute = $makeAbsolute($href);
            return $matches[1] . $proxyBase . urlencode($absolute) . $matches[3];
        },
        $html
    );

    // 5. Rewrite <form action="..."> to go through proxy
    $html = preg_replace_callback(
        '/(<form\s[^>]*action=["\'])([^"\']*?)(["\'])/i',
        function ($matches) use ($proxyBase, $makeAbsolute) {
            $action = $matches[2];
            if (empty($action) || $action[0] === '#') {
                return $matches[0];
            }
            $absolute = $makeAbsolute($action);
            return $matches[1] . $proxyBase . urlencode($absolute) . $matches[3];
        },
        $html
    );

    // 6. Strip CSP and X-Frame-Options meta tags
    $html = preg_replace('/<meta[^>]*http-equiv=["\']Content-Security-Policy["\'][^>]*>/i', '', $html);
    $html = preg_replace('/<meta[^>]*http-equiv=["\']X-Frame-Options["\'][^>]*>/i', '', $html);

    // 7. Inject a small script to intercept clicks and keep navigation inside proxy
    $interceptScript = <<<'JS'
<script>
document.addEventListener('click', function(e) {
    var a = e.target.closest('a[href]');
    if (!a) return;
    var href = a.getAttribute('href');
    if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return;
    // If already going through proxy, let it happen
    if (href.indexOf('/api/proxy') === 0) return;
    // Make absolute and redirect through proxy
    e.preventDefault();
    var abs = href;
    if (href.indexOf('http') !== 0) {
        if (href.indexOf('//') === 0) abs = 'https:' + href;
        else if (href.charAt(0) === '/') abs = location.protocol + '//' + location.host.replace(/:.*/,'') + href;
        else abs = document.baseURI.replace(/[^\/]*$/, '') + href;
    }
    window.location.href = '/api/proxy?url=' + encodeURIComponent(abs);
});
</script>
JS;

    // Inject before </body> or at end
    if (stripos($html, '</body>') !== false) {
        $html = str_ireplace('</body>', $interceptScript . '</body>', $html);
    } else {
        $html .= $interceptScript;
    }

    return new Response(
        content: $html,
        status: 200,
        headers: [
            'Content-Type' => 'text/html; charset=utf-8',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Content-Security-Policy' => "frame-ancestors 'self'",
        ],
    );
});
