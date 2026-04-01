<?php

namespace App\Filament\Clusters\Authentication\Resources\Users\Pages;

use App\Filament\Clusters\Authentication\Resources\Users\UserResource;
use App\Traits\Filament\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    use ListPage;

    protected static string $resource = UserResource::class;
}
