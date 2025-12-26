<?php
namespace App\Utils;

class ResponseTool
{
    public static function jsonResponse(int $code, string $message, $data = null, int $httpStatus = 200)
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $httpStatus);
    }
}