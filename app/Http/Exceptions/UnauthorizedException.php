<?php

namespace App\Http\Exceptions;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UnauthorizedException extends UnauthorizedHttpException
{
    /**
     * @OA\Schema(
     *   schema="UnauthorizedException",
     *   @OA\Property(property="success", type="boolean", default=false, example=false),
     *   @OA\Property(property="message", type="string", example="Unauthorized request"),
     *   @OA\Property(property="code", type="integer", example=403)
     * )
     */
    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized request',
            'code' => JsonResponse::HTTP_FORBIDDEN,
        ], JsonResponse::HTTP_FORBIDDEN);
    }
}
