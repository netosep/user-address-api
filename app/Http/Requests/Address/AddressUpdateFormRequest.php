<?php

namespace App\Http\Requests\Address;

use App\Http\Requests\FormRequest;

/**
 * @OA\Schema(
 *   schema="AddressUpdateRequest",
 *   @OA\Property(property="street", type="string", example="Hollywood Blvd"),
 *   @OA\Property(property="number", type="string", example="1923"),
 *   @OA\Property(property="neighborhood", type="string", example="Union"),
 *   @OA\Property(property="city", type="string", example="Hollywood"),
 *   @OA\Property(property="state", type="string", example="Florida"),
 *   @OA\Property(property="country", type="string", example="US"),
 *   @OA\Property(property="zip_code", type="string", example="33020")
 * )
 */
class AddressUpdateFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'street' => 'sometimes|required|max:255',
            'number' => 'sometimes|required|max:10',
            'neighborhood' => 'sometimes|required|max:255',
            'city' => 'sometimes|required|max:255',
            'state' => 'sometimes|required|max:255',
            'country' => 'sometimes|required|min:2|max:2',
            'zip_code' => 'sometimes|required|numeric|max_digits:10',
        ];
    }
}
