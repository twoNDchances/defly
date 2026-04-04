<?php

namespace App\Filament\Clusters\Authentication\Resources\Users\Schemas;

use App\Traits\Filament\Specifics\User\UserField;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;

class UserForm
{
    use UserField;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Components\Section::make(__('forms.user.sections.a.title'))
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
                        Components\Section::make(__('forms.commons.sections.management'))
                            ->columnSpan(1)
                            ->columns(1)
                            ->schema([
                                self::canManageFromOther(),
                            ]),
                    ]),
            ]);
    }
}
