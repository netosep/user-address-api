<?php

namespace App\Http\Exceptions;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InvalidCredentialsException extends BadRequestHttpException
{
    /**
     * @OA\Schema(
     *   schema="InvalidCredentialsException",
     *   @OA\Property(property="success", type="boolean", default=false, example=false),
     *   @OA\Property(property="message", type="string", example="Invalid credentials"),
     *   @OA\Property(property="code", type="integer", example=401)
     * )
     */
    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => empty($this->message) ? 'Invalid credentials' : $this->message,
            'code' => JsonResponse::HTTP_UNAUTHORIZED,
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
