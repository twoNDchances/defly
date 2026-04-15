<?php

namespace App\Filament\Clusters\AccessControl\Resources\Groups\Schemas;

use App\Filament\Components\Group\GroupForm as Form;
use Filament\Schemas\Schema;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
