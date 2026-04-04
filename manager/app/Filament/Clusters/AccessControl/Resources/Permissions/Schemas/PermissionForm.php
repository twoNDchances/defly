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
        return $schema->components(self::fields());
    }

    public static function fields()
    {
        return [
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
                    Components\Section::make(__('forms.commons.sections.labels.title'))
                        ->columnSpan(1)
                        ->columns(1)
                        ->schema([
                            self::labels(),
                        ]),
                ]),
        ];
    }
}
