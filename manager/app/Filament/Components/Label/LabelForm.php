<?php

namespace App\Filament\Components\Label;

use App\Traits\Filament\Specifics\Label\LabelField;
use Filament\Schemas\Components\Section;

class LabelForm
{
    use LabelField;

    public static function build()
    {
        return [
            Section::make(__('forms.label.sections.a.title'))
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    self::setName(),
                    self::setColor(),
                    self::setDescriptionField()->columnSpanFull(),
                ]),
        ];
    }
}
