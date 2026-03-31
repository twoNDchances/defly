<?php

namespace App\Filament\Clusters\Authentication\Resources\Users\Pages;

use App\Filament\Clusters\Authentication\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
