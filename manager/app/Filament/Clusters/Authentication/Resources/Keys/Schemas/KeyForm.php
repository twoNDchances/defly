<?php

namespace App\Filament\Clusters\Authentication\Resources\Keys\Schemas;

use App\Filament\Components\Key\KeyForm as Form;
use Filament\Schemas\Schema;

class KeyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
