<?php

namespace App\Filament\Components\Group;

use App\Traits\Filament\Specifics\Group\GroupField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class GroupForm
{
    use GroupField;

    public static function build()
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Section::make(__('forms.group.sections.a.title'))
                        ->columnSpan(2)
                        ->columns(1)
                        ->schema([
                            self::setName(),
                            self::setDescriptionField(),
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
