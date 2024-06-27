<?php

namespace App\Http\Exceptions;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundException extends NotFoundHttpException
{
    /**
     * @OA\Schema(
     *   schema="NotFoundException",
     *   @OA\Property(property="success", type="boolean", default=false, example=false),
     *   @OA\Property(property="message", type="string", example="Item not found"),
     *   @OA\Property(property="code", type="integer", example=404)
     * )
     */
    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage() ?: 'Item not found',
            'code' => JsonResponse::HTTP_NOT_FOUND,
        ], JsonResponse::HTTP_NOT_FOUND);
    }
}
