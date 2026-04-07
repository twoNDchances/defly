<?php

namespace App\Filament\Clusters\Context\Resources\Engines\Schemas;

use App\Filament\Components\Engine\EngineForm as Form;
use Filament\Schemas\Schema;

class EngineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
