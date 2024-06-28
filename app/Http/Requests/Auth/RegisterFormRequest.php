<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\FormRequest;

/**
 * @OA\Schema(
 *   schema="RegisterRequest",
 *   @OA\Property(property="name", type="string", example="John Doe"),
 *   @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *   @OA\Property(property="password", type="string", example="12345"),
 *   @OA\Property(property="password_confirmation", type="string", example="12345")
 * )
 */
class RegisterFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:5|max:255',
            'password_confirmation' => 'required|string|min:5|max:255|same:password',
        ];
    }
}
