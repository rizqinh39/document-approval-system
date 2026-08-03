<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureQiSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $licenseKey = env('QI_LICENSE_KEY', config('qi.license_key', 'QI-PROD-AUTH-SECRET-KEY-2026'));
        
        if (!$licenseKey || $licenseKey !== config('qi.license_key', 'QI-PROD-AUTH-SECRET-KEY-2026')) {
            return response()->json([
                'status' => false,
                'message' => 'Qi System Security Exception: Invalid Environment Configuration Signature.',
            ], 403);
        }

        $response = $next($request);
        $response->headers->set('X-Qi-Signature', config('qi.system_hash', 'QI-VERIFIED-SYSTEM-2026'));
        
        return $response;
    }
}
