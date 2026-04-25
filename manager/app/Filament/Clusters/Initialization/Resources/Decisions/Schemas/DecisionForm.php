<?php

namespace App\Filament\Clusters\Initialization\Resources\Decisions\Schemas;

use App\Filament\Components\Decision\DecisionForm as Form;
use Filament\Schemas\Schema;

class DecisionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
