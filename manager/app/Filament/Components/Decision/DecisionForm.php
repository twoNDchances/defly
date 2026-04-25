<?php

namespace App\Filament\Components\Decision;

use App\Traits\Filament\Specifics\Decision\DecisionField;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class DecisionForm
{
    use DecisionField;

    public static function build()
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Section::make()
                        ->columnSpan(2)
                        ->columns(2)
                        ->schema([
                            self::setName(),
                            self::setDirection(),
                            self::setCondition(),
                            self::setScore(),
                            self::setAction()->columnSpanFull(),
                            Fieldset::make(__('models.decision.fields.configurations'))
                                ->columnSpanFull()
                                ->columns(2)
                                ->schema([
                                    self::setDenyDirective(),
                                    self::setDenyRecord(),
                                    self::setRewriteHeadersDirective()->columnSpanFull(),
                                    self::setRewriteHeadersSet()->columnSpanFull(),
                                    self::setRewriteHeadersUnset()->columnSpanFull(),
                                    self::setRewriteBodyDirective()->columnSpanFull(),
                                    self::setRewriteBodySet()->columnSpanFull(),
                                    self::setRewriteBodyUnset()->columnSpanFull(),
                                    self::setRewriteType(),
                                    self::setRewritePath(),
                                    self::setRewriteQueryDirective(),
                                    self::setRewriteQuerySet()->columnSpanFull(),
                                    self::setRewriteQueryUnset()->columnSpanFull(),
                                    self::setRedirectUrl()->columnSpanFull(),
                                    self::setSavePosition(),
                                    self::setSaveName(),
                                ]),
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
