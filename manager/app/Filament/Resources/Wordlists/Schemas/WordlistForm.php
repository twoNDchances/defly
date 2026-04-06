<?php

namespace App\Filament\Resources\Wordlists\Schemas;

use App\Filament\Components\Wordlist\WordlistForm as Form;
use Filament\Schemas\Schema;

class WordlistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(Form::build());
    }
}
