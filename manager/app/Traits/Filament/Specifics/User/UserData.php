<?php

namespace App\Traits\Filament\Specifics\User;

trait UserData
{
    public static function editForm($data): array
    {
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }
}
