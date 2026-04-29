<?php

namespace App\Filament\Components\Principle;

use App\Traits\Filament\Specifics\Principle\PrincipleField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class PrincipleForm
{
    use PrincipleField;

    public static function build()
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Tabs::make()
                        ->columnSpan(2)
                        ->tabs([
                            Tab::make(__('forms.principle.tabs.a.title'))
                                ->columns(2)
                                ->schema([
                                    self::setName(),
                                    self::setLevel(),
                                    self::setPhase()->columnSpanFull(),
                                    self::setDescription()->columnSpanFull(),
                                ]),

                            Tab::make(__('forms.principle.tabs.b.title'))
                                ->disabledOn('create')
                                ->visibleOn(['view', 'edit'])
                                ->columns(1)
                                ->schema([
                                    self::setValidationStatus(),
                                    self::setValidationDetails(),
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
