<?php

namespace App\Filament\Components\Defender;

use App\Traits\Filament\Specifics\Defender\DefenderField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class DefenderForm
{
    use DefenderField;

    public static function build()
    {
        return [
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    Grid::make(1)
                        ->schema([
                            Tabs::make()
                                ->columns(1)
                                ->schema([
                                    Tab::make(__('forms.defender.tabs.a.title'))
                                        ->schema([
                                            self::setName(),
                                            self::setDescription(),
                                        ]),

                                    Tab::make(__('forms.defender.tabs.b.title'))
                                        ->visibleOn(['view', 'edit'])
                                        ->schema([
                                            self::setStatus(),
                                            self::setDetails(),
                                            self::setDeploymentStatus(),
                                            self::setDeploymentDetails(),
                                        ]),
                                ]),

                            Section::make(__('forms.generals.bases.sections.labels.title'))
                                ->columns(1)
                                ->schema([
                                    self::setLabels(),
                                ]),
                        ]),

                    Tabs::make()
                        ->columns(1)
                        ->schema([
                            Tab::make(__('forms.defender.tabs.c.title'))
                                ->schema([
                                    self::setCommonEnvironmentVariables(),
                                ]),

                            Tab::make(__('forms.defender.tabs.d.title'))
                                ->schema([
                                    self::setServerEnvironmentVariables(),
                                ]),

                            Tab::make(__('forms.defender.tabs.e.title'))
                                ->schema([
                                    self::setProxyEnvironmentVariables(),
                                ]),
                        ]),
                ]),
        ];
    }
}
