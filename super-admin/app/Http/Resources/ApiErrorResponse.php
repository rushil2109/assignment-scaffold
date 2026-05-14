<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;

class ApiErrorResponse
{
    public static function make(string $error): JsonResponse
    {
        return new JsonResponse(['ok' => false, 'error' => $error], 200);
    }
}
