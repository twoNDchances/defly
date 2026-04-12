<?php

namespace App\Filament\Clusters\Context\Resources\Targets\Schemas;

use App\Filament\Components\Target\TargetForm as Form;
use Filament\Schemas\Schema;

class TargetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
