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
