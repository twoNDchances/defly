<?php

namespace App\Filament\Clusters\AccessControl\Resources\Permissions\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('applied_for')
                    ->required(),
                TextInput::make('action')
                    ->required(),
                TextInput::make('created_by')
                    ->default(null),
            ]);
    }
}
