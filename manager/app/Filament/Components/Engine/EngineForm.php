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
                            self::name()->columnSpanFull(),
                            self::inputDatatype()->columnSpan(1),
                            Grid::make(1)
                                ->columnSpan(2)
                                ->schema([
                                    Fieldset::make(__('forms.engine.sections.a.fieldsets.a.title'))
                                        ->columnSpanFull()
                                        ->columns(1)
                                        ->schema([
                                            self::type()->columnSpanFull(),
                                            Fieldset::make(__('forms.engine.sections.a.fieldsets.b.title'))
                                                ->columnSpanFull()
                                                ->columns(1)
                                                ->schema([
                                                    self::position(),
                                                    self::digit(),
                                                    self::hashMethod(),
                                                    self::separator(),
                                                ]),
                                        ]),
                                ]),
                            self::outputDatatype()->columnSpan(1),
                        ]),
                    Section::make(__('forms.commons.sections.labels.title'))
                        ->columnSpan(1)
                        ->columns(1)
                        ->schema([
                            self::labels(),
                        ]),
                ]),
        ];
    }
}
