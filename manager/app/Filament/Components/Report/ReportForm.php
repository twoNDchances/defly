<?php

namespace App\Filament\Components\Report;

use App\Traits\Filament\Specifics\Report\ReportField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class ReportForm
{
    use ReportField;

    public static function build()
    {
        return [
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    Section::make(__('forms.report.sections.metas.title'))
                        ->collapsed()
                        ->columnSpanFull()
                        ->columns(4)
                        ->schema([
                            self::setTriggeredBy(),
                            self::setMetaIp(),
                            self::setMetaProtocol(),
                            self::setMetaStatus(),
                            self::setMetaMethod()->columnSpan(2),
                            self::setMetaUrl()->columnSpan(2),
                        ]),

                    Section::make(__('forms.report.sections.request.title'))
                        ->collapsed()
                        ->columnSpan(1)
                        ->columns(1)
                        ->schema([
                            Tabs::make()
                                ->contained(false)
                                ->schema([
                                    Tab::make(__('models.report.fields.request_headers'))
                                        ->schema([
                                            self::setRequestHeaders(),
                                        ]),
                                    Tab::make(__('models.report.fields.request_body'))
                                        ->schema([
                                            self::setRequestBody(),
                                        ]),
                                ]),
                        ]),

                    Section::make(__('forms.report.sections.response.title'))
                        ->collapsed()
                        ->columnSpan(1)
                        ->columns(1)
                        ->schema([
                            Tabs::make()
                                ->contained(false)
                                ->schema([
                                    Tab::make(__('models.report.fields.response_headers'))
                                        ->schema([
                                            self::setResponseHeaders(),
                                        ]),
                                    Tab::make(__('models.report.fields.response_body'))
                                        ->schema([
                                            self::setResponseBody(),
                                        ]),
                                ]),
                        ]),

                    Section::make(__('forms.report.sections.rule.title'))
                        ->collapsed()
                        ->columnSpanFull()
                        ->columns(1)
                        ->schema([
                            self::setRuleDetails(),
                        ]),
                ]),
        ];
    }
}
