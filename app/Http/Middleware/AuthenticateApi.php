<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticateApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('sanctum')->check() || auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            $token = $user->currentAccessToken();
            
            if ($token->expires_at && Carbon::now()->greaterThan($token->expires_at)) {
                $token->delete();
                return response()->json(['message' => 'Token has expired.'], 401);
            }
            $expiration = Carbon::now()->addDays(7);
            $user->tokens()->where('tokenable_id', $user->id)->update(['expires_at' => $expiration]);

            return $next($request);
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
