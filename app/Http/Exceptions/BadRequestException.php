<?php

namespace App\Http\Exceptions;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BadRequestException extends BadRequestHttpException
{
    /**
     * @OA\Schema(
     *   schema="BadRequestException",
     *   @OA\Property(property="success", type="boolean", default=false, example=false),
     *   @OA\Property(property="message", type="string", example="Bad request"),
     *   @OA\Property(property="code", type="integer", example=400)
     * )
     */
    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage() ?: 'Bad request',
            'code' => JsonResponse::HTTP_BAD_REQUEST,
        ], JsonResponse::HTTP_BAD_REQUEST);
    }
}
