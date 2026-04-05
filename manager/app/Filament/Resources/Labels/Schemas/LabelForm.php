<?php

namespace App\Filament\Resources\Labels\Schemas;

use App\Filament\Components\Label\LabelForm as Form;
use Filament\Schemas\Schema;

class LabelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
