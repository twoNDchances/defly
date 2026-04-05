<?php

namespace App\Filament\Clusters\Authentication\Resources\Users\Schemas;

use App\Filament\Components\User\UserForm as Form;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
