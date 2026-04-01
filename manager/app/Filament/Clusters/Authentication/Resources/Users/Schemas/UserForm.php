<?php

namespace App\Filament\Clusters\Authentication\Resources\Users\Schemas;

use App\Traits\Filament\Fields\UserField;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;

class UserForm
{
    use UserField;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::main());
    }

    public static function main($policy = true)
    {
        return [
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
                            // Components\Fieldset::make('Verification')
                            // ->columnSpan(5)
                            // ->columns(1)
                            // ->schema([
                            //     self::mustVerify(),
                            //     self::token(),
                            // ]),
                        ]),

                    // Components\Grid::make(1)
                    // ->columnSpan(1)
                    // ->schema([
                    //     Components\Section::make('User Policies')
                    //     ->columns(1)
                    //     ->schema([
                    //         self::policies($policy),
                    //     ]),

                    //     Components\Section::make('User Labels')
                    //     ->columns(1)
                    //     ->schema([
                    //         self::labels(),
                    //     ]),
                    // ]),
                ]),
        ];
    }
}
