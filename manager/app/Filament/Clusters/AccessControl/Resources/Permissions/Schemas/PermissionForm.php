<?php

namespace App\Filament\Clusters\AccessControl\Resources\Permissions\Schemas;

use App\Filament\Components\Permission\PermissionForm as Form;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
