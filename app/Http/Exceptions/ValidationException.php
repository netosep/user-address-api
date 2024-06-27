<?php

namespace App\Http\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException as BaseValidationException;

class ValidationException extends BaseValidationException
{
    /**
     * @OA\Schema(
     *   schema="ValidationException",
     *   @OA\Property(property="success", type="boolean", default=false, example=false),
     *   @OA\Property(property="message", type="string", example="Validation error"),
     *   @OA\Property(property="code", type="integer", example=422),
     *   @OA\Property(
     *     property="fields",
     *     type="object",
     *     @OA\Property(
     *       property="field_name",
     *       type="array",
     *       @OA\Items(type="string", example="The field_name is required.")
     *     )
     *   )
     * )
     */
    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'code' => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            'fields' => $this->validator->errors()->toArray()
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }
}
