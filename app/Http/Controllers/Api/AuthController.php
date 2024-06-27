<?php

namespace App\Http\Controllers\Api;

use App\Http\Exceptions\InvalidCredentialsException;
use App\Http\Requests\Auth\LoginFormRequest;
use App\Http\Requests\Auth\RegisterFormRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
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
     *     @OA\JsonContent(type="object", ref="#/components/schemas/TokenResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Invalid credentials",
     *     @OA\JsonContent(type="object", ref="#/components/schemas/InvalidCredentialsException")
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(type="object", ref="#/components/schemas/ValidationException")
     *   ),
     * )
     */
    public function auth(Request $request)
    {
        $this->validateRequest(LoginFormRequest::class, $request);

        $credentials = $request->only(['email', 'password']);

        /** @var User */
        $user = User::firstWhere('email', $credentials['email']);
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new InvalidCredentialsException();
        }

        # delete old tokens
        $user->tokens()->delete();

        $expiresAt = now()->addWeek(); // 7 days
        $token = $user->createToken(name: 'user-token', expiresAt: $expiresAt)->plainTextToken;

        return $this->tokenResponse($token, $expiresAt);
    }

    /**
     * @OA\Post(
     *   tags={"Auth"},
     *   path="/api/register",
     *   summary="Register",
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="name", type="string", example="John Doe"),
     *       @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *       @OA\Property(property="password", type="string", example="12345"),
     *       @OA\Property(property="password_confirmation", type="string", example="12345")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="AccessToken Response",
     *     @OA\JsonContent(type="object", ref="#/components/schemas/TokenResponse")
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(type="object", ref="#/components/schemas/ValidationException")
     *   ),
     * )
     */
    public function register(Request $request)
    {
        $this->validateRequest(RegisterFormRequest::class, $request);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' =>  Hash::make($request->password),
        ]);

        $expiresAt = now()->addWeek(); // 7 days
        $token = $user->createToken(name: 'user-token', expiresAt: $expiresAt)->plainTextToken;

        return $this->tokenResponse($token, $expiresAt, 'User register successfully', JsonResponse::HTTP_CREATED);
    }

    /**
     * @OA\Post(
     *   tags={"Auth"},
     *   path="/api/logout",
     *   summary="Logout",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="Logout Response",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="success", type="boolean", default=true, example=true),
     *       @OA\Property(property="message", type="string", example="Successfully logged out"),
     *       @OA\Property(property="code", type="integer", example=200)
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
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->jsonResponse(message: 'Successfully logged out');
    }

    /**
     * @OA\Schema(
     *   schema="TokenResponse",
     *   @OA\Property(property="message", type="string"),
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
    public function tokenResponse(string $token, \DateTime $expiresIn, string $message = null, int $code = JsonResponse::HTTP_OK)
    {
        $data = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expiresIn->getTimestamp(),
        ];

        return $this->jsonResponse($data, $message, $code);
    }
}
