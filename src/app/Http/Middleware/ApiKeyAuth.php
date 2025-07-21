<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next)
    {
        $headerName = env('API_KEY_HEADER_NAME', 'x-api-token');
        $validKey = env('API_KEY');

        if ($request->header($headerName) !== $validKey) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
