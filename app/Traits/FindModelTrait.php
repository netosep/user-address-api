<?php

namespace App\Traits;

use App\Http\Exceptions\NotFoundException;

trait FindModelTrait
{
    protected function findOrFail($model, $id, $userId = null)
    {
        $query = $model::where('id', $id);

        if ($userId) {
            $query = $query->where('user_id', $userId);
        }

        $record = $query->first();

        if (!$record) {
            throw new NotFoundException();
        }

        return $record;
    }
}
