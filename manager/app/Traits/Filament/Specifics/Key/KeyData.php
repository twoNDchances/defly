<?php

namespace App\Traits\Filament\Specifics\Key;

trait KeyData
{
    public static function editForm($data)
    {
        if (blank($data['token'] ?? null)) {
            unset($data['token']);
        }

        return $data;
    }
}
