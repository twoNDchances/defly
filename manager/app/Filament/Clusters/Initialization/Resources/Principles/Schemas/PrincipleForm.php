<?php

namespace App\Filament\Clusters\Initialization\Resources\Principles\Schemas;

use App\Filament\Components\Principle\PrincipleForm as Form;
use Filament\Schemas\Schema;

class PrincipleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
