<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authenticate = true;
        $tokenHeader = $request->header('authorization');
        $user = null;

        if (!$tokenHeader) {
            $authenticate = false;
        }

        $user = User::where('token', $tokenHeader)->first();

        if (!$user) {
            $authenticate = false;
        }

        if ($authenticate) {
            Auth::login($user);
            return $next($request);
        } else {
            return response()->json([
                "errors" => [
                    "message" => "This action is unauthorized",
                ]
            ], 401);
        }
    }
}
