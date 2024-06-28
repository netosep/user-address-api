<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\User\UserUpdateFormRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    /**
     * @OA\Get(
     *   tags={"User"},
     *   path="/api/user",
     *   summary="Show User information",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="User information response",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="success", type="boolean", default=true, example=true),
     *       @OA\Property(property="code", type="integer", example=200),
     *       @OA\Property(property="result", type="object", ref="#/components/schemas/User")
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated response",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *     )
     *   )
     * )
     */
    public function index(Request $request)
    {
        $user = User::find($request->user()->id);
        $addresses = $user->addresses()->paginate(self::ITEMS_PER_PAGE);
        $user->addresses = $this->transformPaginate($addresses);

        return $this->jsonResponse($user);
    }

    /**
     * @OA\Put(
     *   tags={"User"},
     *   path="/api/user",
     *   summary="Update User information",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     @OA\JsonContent(type="object", ref="#/components/schemas/UserRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Update User information response",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="User updated successfully"),
     *       @OA\Property(property="success", type="boolean", default=true, example=true),
     *       @OA\Property(property="code", type="integer", example=200),
     *       @OA\Property(
     *         property="result",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="name", type="string", example="John Doe"),
     *         @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *         @OA\Property(property="created_at", type="datetime"),
     *         @OA\Property(property="updated_at", type="datetime")
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad request response",
     *     @OA\JsonContent(type="object", ref="#/components/schemas/BadRequestException")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated response",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error response",
     *     @OA\JsonContent(type="object", ref="#/components/schemas/ValidationException")
     *   )
     * )
     */
    public function update(Request $request)
    {
        $validator = $this->validateRequest(UserUpdateFormRequest::class, $request);
        $this->validateExistsFieldRequest($request, $validator);

        $user = $request->user();
        $user->fill($request->except('password'));
        $user->save();

        return $this->jsonResponse($user, 'User updated successfully');
    }

    /**
     * @OA\Delete(
     *   tags={"User"},
     *   path="/api/user",
     *   summary="Delete current User",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="User deleted response",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="Item deleted successfully"),
     *       @OA\Property(property="success", type="boolean", default=true, example=true),
     *       @OA\Property(property="code", type="integer", example=200)
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated response",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *     )
     *   )
     * )
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return $this->jsonResponse(message: 'Item deleted successfully');
    }
}
