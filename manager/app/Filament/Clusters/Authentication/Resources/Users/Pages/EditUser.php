<?php

namespace App\Filament\Clusters\Authentication\Resources\Users\Pages;

use App\Filament\Clusters\Authentication\Resources\Users\UserResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use App\Traits\Filament\Specifics\User\UserData;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use EditPage;
    use UserData;

    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return self::saveForm($data);
    }
}
