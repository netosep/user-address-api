<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="Address",
 *   @OA\Property(property="id", type="integer", example=999),
 *   @OA\Property(property="user_id", type="integer", example=999),
 *   @OA\Property(property="street", type="string", example="Hollywood Blvd"),
 *   @OA\Property(property="number", type="string", example="1923"),
 *   @OA\Property(property="neighborhood", type="string", example="Union"),
 *   @OA\Property(property="city", type="string", example="Hollywood"),
 *   @OA\Property(property="state", type="string", example="Florida"),
 *   @OA\Property(property="country", type="string", example="US"),
 *   @OA\Property(property="zip_code", type="string", example="33020"),
 *   @OA\Property(property="created_at", type="datetime"),
 *   @OA\Property(property="updated_at", type="datetime")
 * )
 */
class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'street',
        'number',
        'neighborhood',
        'city',
        'state',
        'country',
        'zip_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
