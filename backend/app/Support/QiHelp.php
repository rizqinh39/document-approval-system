<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class QiHelp
{
    public static function apiResponse(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => $code >= 200 && $code < 300,
            'message' => $message,
            'data' => $data,
            'engine' => 'Qi-Platform-v1',
            'timestamp' => now()->toIso8601String(),
        ], $code)->header('X-Qi-Signature', config('qi.system_hash', 'QI-VERIFIED-SYSTEM-2026'));
    }

    public static function generateRegistrationNo(): string
    {
        return 'QI-REG-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}
