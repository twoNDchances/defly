<?php

namespace App\Filament\Components\Pattern;

use App\Traits\Filament\Specifics\Pattern\PatternField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class PatternForm
{
    use PatternField;

    public static function build()
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Section::make(__('forms.pattern.sections.a.title'))
                        ->columnSpan(2)
                        ->columns(2)
                        ->schema([
                            self::setPhase(),
                            self::setType(),
                            self::setName(),
                            self::setDatatype(),
                            self::setDescriptionField()->columnSpanFull(),
                        ]),

                    Section::make(__('forms.pattern.sections.b.title'))
                        ->columnSpan(1)
                        ->columns(1)
                        ->schema([
                            self::setTargets(),
                        ]),
                ]),
        ];
    }
}
