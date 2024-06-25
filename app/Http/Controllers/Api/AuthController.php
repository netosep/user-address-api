<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Exceptions\InvalidCredentialsException;
use App\Http\Exceptions\ValidationException;
use App\Http\Requests\Auth\LoginFormRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Info(title="User Addresses API", version="1.0")
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *   tags={"Auth"},
     *   path="/api/login",
     *   summary="Login",
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *       @OA\Property(property="password", type="string", example="12345")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="AccessToken Response",
     *     @OA\JsonContent(
     *       type="object",
     *       ref="#/components/schemas/TokenResponse"
     *      )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Invalid credentials",
     *     @OA\JsonContent(
     *       type="object",
     *       ref="#/components/schemas/InvalidCredentialsException"
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(
     *       type="object",
     *       ref="#/components/schemas/ValidationException"
     *     )
     *   ),
     * )
     */
    public function auth(Request $request)
    {
        $validator = $this->makeValidator(LoginFormRequest::class, $request);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $credentials = $request->only(['email', 'password']);

        /** @var User */
        $user = User::firstWhere('email', $credentials['email']);
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new InvalidCredentialsException();
        }

        # delete old tokens
        // $user->tokens()->delete();

        $expiresAt = now()->addWeek(); // 7 days
        $token = $user->createToken(name: 'user-token', expiresAt: $expiresAt)->plainTextToken;

        return $this->tokenResponse($token, $expiresAt);
    }

    public function register(Request $request)
    {
        // do something
    }

    /**
     * @OA\Schema(
     *   schema="TokenResponse",
     *   @OA\Property(property="success", type="boolean", example=true),
     *   @OA\Property(property="code", type="integer", example=200),
     *   @OA\Property(
     *     property="result",
     *     type="object",
     *     @OA\Property(property="access_token", type="string", example="1|eyawlseihg5&hrtbsdfffs4aw..."),
     *     @OA\Property(property="token_type", type="string", example="Bearer"),
     *     @OA\Property(property="expires_in", type="integer", example=178569842)
     *   )
     * )
     */
    protected function tokenResponse(string $token, \DateTime $expiresIn)
    {
        $data = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expiresIn->getTimestamp(),
        ];

        return $this->jsonResponse($data);
    }

    public function me(Request $request)
    {
        return $this->jsonResponse(['me' => $request->user()]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->jsonResponse(message: 'Successfully logged out');
    }
}
