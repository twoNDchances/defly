<?php

namespace App\Filament\Components\Target;

use App\Traits\Filament\Specifics\Target\TargetField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;

class TargetForm
{
    use TargetField;

    public static function build()
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Wizard::make([
                        Step::make(__('forms.target.steps.a.title'))
                            ->schema([
                                self::setPhase(),
                                Grid::make(1)
                                    ->schema([
                                        self::setType(),
                                        self::setPattern(),
                                    ]),
                            ]),

                        Step::make(__('forms.target.steps.b.title'))
                            ->schema([
                                self::setName(),
                                Grid::make(1)
                                    ->schema([
                                        self::setDatatype(),
                                        self::setWordlist(),
                                    ]),
                                self::setDescriptionField()->columnSpanFull(),
                            ]),
                    ])
                        ->columnSpan(2)
                        ->columns(2),

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
