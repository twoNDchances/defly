<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Guards\Schemas;

use App\Filament\Components\Guard\GuardForm as Form;
use Filament\Schemas\Schema;

class GuardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
