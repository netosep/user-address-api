<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *   schema="User",
 *   @OA\Property(property="id", type="integer", example=999),
 *   @OA\Property(property="name", type="string", example="Joe Snow"),
 *   @OA\Property(property="email", type="string", example="joe@example.com"),
 *   @OA\Property(property="created_at", type="datetime"),
 *   @OA\Property(property="updated_at", type="datetime"),
 *   @OA\Property(
 *     property="addresses",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="total", type="integer", example=1),
 *     @OA\Property(property="per_page", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=1),
 *     @OA\Property(property="data", type="array",  @OA\Items(ref="#/components/schemas/Address"))
 *   )
 * )
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
