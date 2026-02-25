<?php

declare(strict_types=1);

use Accelio\Http\Request;

// Serve the Svelte SPA for the root route
$router->get('/', function (Request $request) {
    $buildIndexPath = __DIR__ . '/../public/build/index.html';
    
    if (file_exists($buildIndexPath)) {
        $html = file_get_contents($buildIndexPath);
        return \Accelio\Http\Response::html($html);
    }
    
    // Fallback: redirect to Vite dev server during development
    return \Accelio\Http\Response::html(
        '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Windows XP</title></head>' .
        '<body><h2>Build not found</h2><p>Run <code>cd frontend && npm run build</code> first, ' .
        'or use <code>npm run dev</code> at <a href="http://localhost:5173">localhost:5173</a></p></body></html>'
    );
});
