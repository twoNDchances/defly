<?php

namespace App\Filament\Components\Policy;

use App\Traits\Filament\Specifics\Policy\PolicyField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class PolicyForm
{
    use PolicyField;

    public static function build()
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Section::make(__('forms.policy.sections.a.title'))
                        ->columnSpan(2)
                        ->columns(1)
                        ->schema([
                            self::name(),
                            self::description(),
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
