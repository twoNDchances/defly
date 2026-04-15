<?php

namespace App\Filament\Components\Permission;

use App\Traits\Filament\Specifics\Permission\PermissionField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class PermissionForm
{
    use PermissionField;

    public static function build()
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Section::make(__('forms.permission.sections.a.title'))
                        ->columnSpan(2)
                        ->columns(2)
                        ->schema([
                            self::setName()->columnSpanFull(),
                            self::setAppliedFor(),
                            self::setAction(),
                            self::setDescriptionField()->columnSpanFull(),
                        ]),
                    Section::make(__('forms.generals.bases.sections.labels.title'))
                        ->columnSpan(1)
                        ->columns(1)
                        ->schema([
                            self::setLabels(),
                        ]),
                ]),
        ];
    }
}
