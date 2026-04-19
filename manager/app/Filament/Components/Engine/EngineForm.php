<?php

namespace App\Filament\Components\Engine;

use App\Traits\Filament\Specifics\Engine\EngineField;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class EngineForm
{
    use EngineField;

    public static function build()
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Section::make(__('forms.engine.sections.a.title'))
                        ->columnSpan(2)
                        ->columns(4)
                        ->schema([
                            self::setName()->columnSpanFull(),
                            self::setInputDatatype()->columnSpan(1),
                            Grid::make(1)
                                ->columnSpan(2)
                                ->schema([
                                    self::setType()->columnSpanFull(),
                                    Fieldset::make(__('models.engine.fields.configurations'))
                                        ->columnSpanFull()
                                        ->columns(1)
                                        ->schema([
                                            self::setPosition(),
                                            self::setDigit(),
                                            self::setHashMethod(),
                                            self::setSeparator(),
                                        ]),
                                ]),
                            self::setOutputDatatype()->columnSpan(1),
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
