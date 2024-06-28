<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\FormRequest;

/**
 * @OA\Schema(
 *   schema="ChangePasswordRequest",
 *   @OA\Property(property="password", type="string", example="12345"),
 *   @OA\Property(property="new_password", type="string", example="12345"),
 *   @OA\Property(property="confirm_new_password", type="string", example="12345"),
 * )
 */
class ChangePasswordFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'password' => 'required|string|min:5|max:255',
            'new_password' => 'required|string|min:5|max:255',
            'confirm_new_password' => 'required|string|min:5|max:255|same:new_password',
        ];
    }
}
