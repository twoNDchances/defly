<?php

namespace App\Filament\Clusters\Initialization\Resources\Rules\Schemas;

use App\Filament\Components\Rule\RuleForm as Form;
use Filament\Schemas\Schema;

class RuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
