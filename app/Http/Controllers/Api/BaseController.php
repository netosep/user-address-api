<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Exceptions\BadRequestException;
use App\Http\Exceptions\ValidationException;
use App\Http\Requests\FormRequest;
use App\Traits\FindModelTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

/**
 * @OA\OpenApi(
 *   @OA\Info(
 *     title="API Documentation",
 *     version="1.0.0"
 *   ),
 *   @OA\Components(
 *     @OA\SecurityScheme(
 *       securityScheme="bearerAuth",
 *       type="http",
 *       scheme="bearer",
 *       bearerFormat="JWT"
 *     )
 *   )
 * )
 */
class BaseController extends Controller
{
    use FindModelTrait;

    const ITEMS_PER_PAGE = 10;

    public function jsonResponse(mixed $result = null, string $message = null, int $code = JsonResponse::HTTP_OK): JsonResponse
    {
        if ($message) {
            $response['message'] = $message;
        }

        $response['success'] = true;
        $response['code'] = $code;

        if ($result) {
            $response['result'] = $result;
        }

        return response()->json($response, $code);
    }

    public function makeValidator(string $class, array|Request $request): ValidationValidator
    {
        $formRequest = new $class;
        if (!$formRequest instanceof FormRequest) {
            throw new \InvalidArgumentException('Class must be instance of FormRequest');
        }

        return Validator::make(is_array($request) ? $request : $request->all(), $formRequest->rules());
    }

    public function validateRequest(string $formRequest, array|Request $request): ValidationValidator
    {
        $validator = $this->makeValidator($formRequest, $request);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator;
    }

    public function validateExistsFieldRequest(Request $request, ValidationValidator $validator): bool
    {
        $ruleKeys = array_keys($validator->getRules());
        $requestKeys = array_keys($request->all());

        if (empty(array_intersect($ruleKeys, $requestKeys))) {
            throw new BadRequestException('Nothing to update');
        }

        return true;
    }

    public function transformPaginate(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'last_page' => $paginator->lastPage(),
            'data' => $paginator->items(),
        ];
    }
}
