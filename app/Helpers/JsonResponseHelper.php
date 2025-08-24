<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class JsonResponseHelper
{
    /**
     * Success response
     */
    public static function success($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Error response
     */
    public static function error(string $message = 'Error', int $status = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }
}
