<?php

namespace App\Filament\Clusters\Context\Resources\Patterns\Schemas;

use App\Filament\Components\Pattern\PatternForm as Form;
use Filament\Schemas\Schema;

class PatternForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
