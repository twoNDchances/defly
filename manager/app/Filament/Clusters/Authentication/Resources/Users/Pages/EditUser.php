<?php

namespace App\Filament\Clusters\Authentication\Resources\Users\Pages;

use App\Filament\Clusters\Authentication\Resources\Users\UserResource;
use App\Traits\Filament\Pages\EditPage;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use EditPage;

    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!isset($data['password']))
        {
            unset($data['password']);
        }
        return $data;
    }
}
