<?php

namespace App\Filament\Clusters\Initialization\Resources\Actions\Schemas;

use App\Filament\Components\Action\ActionForm as Form;
use Filament\Schemas\Schema;

class ActionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
