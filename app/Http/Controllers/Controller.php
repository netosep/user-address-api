<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

abstract class Controller
{
    public function makeValidator(string $class, array|Request $request)
    {
        $formRequest = new $class;
        if (!$formRequest instanceof FormRequest) {
            throw new \InvalidArgumentException('Class must be instance of FormRequest');
        }

        return Validator::make(is_array($request) ? $request : $request->all(), $formRequest->rules());
    }

    public function jsonResponse(mixed $result = null, string $message = null): JsonResponse
    {
        $response = [
            'success' => true,
            'code' => JsonResponse::HTTP_OK,
        ];
        $result ? $response['result'] = $result : null;
        $message ? $response['message'] = $message : null;

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
