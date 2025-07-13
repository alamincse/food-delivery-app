<?php

namespace App\Http\Response;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public function validationError($message = 'Validation errors!', $errors = [], $status = 'Error', $statusCode = 422): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => 'FAILED',
            'data' => $errors,
            'statusCode' => '400' . $statusCode,
        ], $statusCode);
    }

    public function success($data = [], $message = null, $responseCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status' => 'SUCCESS',
            'statusCode' => '400200',
            'data' => $data,
            'message' => $message,
        ], $responseCode);
    }

    public function error($message = null, $data = [], $responseCode = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'status' => 'FAILED',
            'statusCode' => '400500',
            'data' => $data,
            'message' => $message,
        ], $responseCode);
    }
}
