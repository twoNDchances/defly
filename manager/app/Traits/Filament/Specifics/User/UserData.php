<?php

namespace App\Traits\Filament\Specifics\User;

trait UserData
{
    public static function saveForm($data): array
    {
        if (! isset($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
