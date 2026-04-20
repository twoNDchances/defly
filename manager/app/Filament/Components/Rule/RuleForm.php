<?php

namespace App\Filament\Components\Rule;

use App\Traits\Filament\Specifics\Rule\RuleField;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;

class RuleForm
{
    use RuleField;

    public static function build()
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Wizard::make([
                        Wizard\Step::make(__('forms.rule.steps.a.title'))
                            ->schema([
                                self::setName(),
                                self::setPhase(),
                                self::setDescription()->columnSpanFull(),
                            ]),

                        Wizard\Step::make(__('forms.rule.steps.b.title'))
                            ->schema([
                                self::setTarget(),
                                self::setComparator(),
                                self::setIsInversed()->columnSpanFull(),
                                self::setWordlist()->columnSpanFull(),
                                Fieldset::make(__('models.rule.fields.configurations'))
                                    ->columnSpanFull()
                                    ->columnSpan(2)
                                    ->schema([
                                        self::setStringValue()->columnSpanFull(),
                                        self::setNumberValue()->columnSpanFull(),
                                        self::setNumberFromValue(),
                                        self::setNumberToValue(),
                                    ]),
                            ]),
                    ])
                        ->columnSpan(2)
                        ->columns(2),

                    Grid::make(1)
                        ->columnSpan(1)
                        ->schema([
                            Section::make(__('forms.generals.bases.sections.labels.title'))
                                ->columnSpan(1)
                                ->columns(1)
                                ->schema([
                                    self::setLabels(),
                                ]),
                        ]),
                ]),
        ];
    }
}
