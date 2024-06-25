<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\FormRequest;

class LoginFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:5|max:255',
        ];
    }
}
