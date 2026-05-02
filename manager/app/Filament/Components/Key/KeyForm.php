<?php

namespace App\Filament\Components\Key;

use App\Traits\Filament\Specifics\Key\KeyField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class KeyForm
{
    use KeyField;

    public static function build()
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Section::make(__('forms.key.sections.a.title'))
                        ->columnSpan(2)
                        ->columns(6)
                        ->schema([
                            self::setName()->columnSpan(3),
                            self::setExpiredAt()->columnSpan(3),
                            self::setToken()->columnSpanFull(),
                            self::setIsReused()->columnSpanFull(),
                        ]),
                    Section::make(__('models.generals.bases.description'))
                        ->columnSpan(1)
                        ->schema([
                            self::setDescriptionField(),
                        ]),
                ]),
        ];
    }
}
