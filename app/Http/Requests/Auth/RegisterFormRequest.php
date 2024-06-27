<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\FormRequest;

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
