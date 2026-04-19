<?php

namespace App\Filament\Components\Action;

use App\Traits\Filament\Specifics\Action\ActionField;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class ActionForm
{
    use ActionField;

    public static function build()
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Section::make(__('forms.action.sections.a.title'))
                        ->columnSpan(2)
                        ->columns(2)
                        ->schema([
                            self::setName(),
                            self::setType(),
                            Fieldset::make(__('models.action.fields.configurations'))
                                ->columnSpanFull()
                                ->columns(2)
                                ->schema([
                                    self::setDenyStatus(),
                                    self::setDenyContentType(),
                                    self::setDenyBody()->columnSpanFull(),
                                    self::setLogFormat()->columnSpanFull(),
                                    self::setLogConsole(),
                                    self::setLogFile(),
                                    self::setRequestUrl(),
                                    self::setRequestMethod(),
                                    self::setRequestHeaders()->columnSpanFull(),
                                    self::setRequestBody()->columnSpanFull(),
                                    self::setSuspectSeverity()->columnSpanFull(),
                                    self::setSetterDirective()->columnSpanFull(),
                                    self::setSetterSet()->columnSpanFull(),
                                    self::setSetterUnset()->columnSpanFull(),
                                    self::setScoreOperator(),
                                    self::setScoreValue(),
                                    self::setLevelOperator(),
                                    self::setLevelValue(),
                                ]),
                            self::setDescription()->columnSpanFull(),
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
