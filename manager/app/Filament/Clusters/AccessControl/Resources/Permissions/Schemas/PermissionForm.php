<?php

namespace App\Filament\Clusters\AccessControl\Resources\Permissions\Schemas;

use App\Traits\Filament\Specifics\Permission\PermissionField;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;

class PermissionForm
{
    use PermissionField;

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Components\Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Components\Section::make(__('forms.permission.sections.a.title'))
                        ->columnSpan(2)
                        ->columns(2)
                        ->schema([
                            self::name()->columnSpanFull(),
                            self::appliedFor(),
                            self::action(),
                            self::description()->columnSpanFull(),
                        ]),
                ]),
        ]);
    }
}
