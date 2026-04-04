<?php

namespace App\Filament\Resources\Labels\Schemas;

use App\Traits\Filament\Specifics\Label\LabelField;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;

class LabelForm
{
    use LabelField;

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::forms());
    }

    public static function forms()
    {
        return [
            Components\Section::make(__('forms.label.sections.a.title'))
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    self::name(),
                    self::color(),
                    self::description()->columnSpanFull(),
                ]),
        ];
    }
}
