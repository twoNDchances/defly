<?php

namespace App\Filament\Clusters\Initialization\Resources\Policies\Schemas;

use App\Filament\Components\Policy\PolicyForm as Form;
use Filament\Schemas\Schema;

class PolicyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
