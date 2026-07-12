<?php

namespace App\Filament\Components\Guard;

use App\Traits\Filament\Specifics\Guard\GuardField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class GuardForm
{
    use GuardField;

    public static function build(): array
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Section::make(__('forms.guard.sections.a.title'))
                        ->columnSpan(2)
                        ->columns(2)
                        ->schema([
                            self::setName(),
                            self::setExpiredAt(),
                            self::setDescriptionField()->columnSpanFull(),
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
