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
                            self::name()->columnSpan(3),
                            self::email()->columnSpan(3),
                            self::password()->columnSpanFull(),
                            self::isActivated()->columnSpan(2),
                            self::isRoot()->columnSpan(2),
                            self::isVerified()->columnSpan(2),
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
