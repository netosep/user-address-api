<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    /**
     * @OA\Get(
     *   tags={"User"},
     *   path="/api/user/me",
     *   summary="User information",
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
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *     )
     *   )
     * )
     */
    public function me(Request $request)
    {
        $user = User::with('addresses')->find($request->user()->id);

        return $this->jsonResponse($user);
    }
}
