<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class CheckTokenExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user('sanctum')->currentAccessToken();
        
        if ($token->expires_at && Carbon::now()->greaterThan($token->expires_at)) {
            $token->delete();
            return response()->json(['message' => 'Token has expired.'], 401);
        }

        return $next($request);
    }
}
