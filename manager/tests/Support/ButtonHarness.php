<?php

namespace Tests\Support;

use App\Traits\Filament\Generals\Components\Button;

class ButtonHarness
{
    use Button;

    public static function createForm(array $data): array
    {
        return [...$data, 'created' => true];
    }

    public static function editForm(array $data): array
    {
        return [...$data, 'edited' => true];
    }

    public static function saveForm(array $data): array
    {
        return [...$data, 'saved' => true];
    }
}
