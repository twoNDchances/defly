<?php

namespace App\Filament\Resources\Timelines\Schemas;

use App\Filament\Components\Timeline\TimelineForm as Form;
use Filament\Schemas\Schema;

class TimelineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
