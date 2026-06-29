<?php

namespace App\Filament\Resources\Defenders\Schemas;

use App\Filament\Components\Defender\DefenderForm as Form;
use Filament\Schemas\Schema;

class DefenderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
