<?php

namespace Tests\Unit\Http\Controllers\Mock;

use App\Http\Requests\FormRequest;

class MockFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'field' => 'required',
        ];
    }
}
