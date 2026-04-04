<?php

namespace App\Filament\Clusters\AccessControl\Resources\Policies\Schemas;

use App\Traits\Filament\Specifics\Policy\PolicyField;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;

class PolicyForm
{
    use PolicyField;

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::fields());
    }

    public static function fields()
    {
        return [
            Components\Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Components\Section::make(__('forms.policy.sections.a.title'))
                        ->columnSpan(2)
                        ->columns(1)
                        ->schema([
                            self::name(),
                            self::description(),
                        ]),
                    Components\Section::make(__('forms.commons.sections.labels.title'))
                        ->columnSpan(1)
                        ->columns(1)
                        ->schema([
                            self::labels(),
                        ]),
                ]),
        ];
    }
}
