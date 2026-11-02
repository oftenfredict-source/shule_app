<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSchoolAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractBearerToken($request);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization token is required.',
            ], 401);
        }

        $payload = Cache::get('school_agent_token:' . $token);

        if (!is_array($payload) || empty($payload['schoolID'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired session token.',
            ], 401);
        }

        $request->attributes->set('school_agent', $payload);
        $request->attributes->set('school_agent_token', $token);

        return $next($request);
    }

    private function extractBearerToken(Request $request): ?string
    {
        $header = (string) $request->header('Authorization', '');
        if ($header === '') {
            $header = (string) ($request->server('REDIRECT_HTTP_AUTHORIZATION')
                ?: $request->server('HTTP_AUTHORIZATION')
                ?: '');
        }
        if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return trim($matches[1]);
        }

        $custom = $request->header('X-School-Agent-Token');
        if (is_string($custom) && trim($custom) !== '') {
            return trim($custom);
        }

        $token = $request->input('token');
        return is_string($token) && $token !== '' ? trim($token) : null;
    }
}
