<?php

namespace App\Filament\Clusters\Authentication\Resources\Users\Pages;

use App\Filament\Clusters\Authentication\Resources\Users\UserResource;
use App\Traits\Filament\Pages\CreatePage;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use CreatePage;

    protected static string $resource = UserResource::class;
}
