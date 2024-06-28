<?php

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *   schema="UserRequest",
 *   @OA\Property(property="name", type="string", example="John Doe"),
 *   @OA\Property(property="email", type="string", format="email", example="john.doe@example.com")
 * )
 */
class UserUpdateFormRequest extends FormRequest
{
    public function rules(): array
    {
        $userId = auth()->user()->id;

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes', 'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            // 'new_password' => 'sometimes|required|string|min:5|max:255',
            // 'password' => 'required_with:new_password|string|min:5|max:255',
            // 'confirm_new_password' => 'required_with:new_password|string|min:5|max:255|same:new_password',
        ];
    }
}
