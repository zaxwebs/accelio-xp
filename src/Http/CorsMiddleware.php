<?php

declare(strict_types=1);

namespace Accelio\Http;

use Closure;

final class CorsMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight OPTIONS request
        if ($request->method() === 'OPTIONS') {
            return new Response(
                content: '',
                status: 204,
                headers: $this->corsHeaders(),
            );
        }

        /** @var Response $response */
        $response = $next($request);

        return $response->withHeaders($this->corsHeaders());
    }

    /**
     * @return array<string, string>
     */
    private function corsHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Accept, X-Trace-Id',
            'Access-Control-Max-Age' => '86400',
        ];
    }
}
