<?php

namespace App\Filament\Components\User;

use App\Traits\Filament\Specifics\User\UserField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class UserForm
{
    use UserField;

    public static function build()
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Section::make(__('forms.user.sections.a.title'))
                        ->columnSpan(2)
                        ->columns(6)
                        ->schema([
                            self::setName()->columnSpan(3),
                            self::setEmail()->columnSpan(3),
                            self::setPassword()->columnSpanFull(),
                            self::setIsActivated()->columnSpan(2),
                            self::setIsRoot()->columnSpan(2),
                            self::setIsVerified()->columnSpan(2),
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
