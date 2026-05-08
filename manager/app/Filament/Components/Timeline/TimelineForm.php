<?php

namespace App\Filament\Components\Timeline;

use App\Traits\Filament\Specifics\Timeline\TimelineField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class TimelineForm
{
    use TimelineField;

    public static function build()
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Section::make(__('forms.timeline.sections.a.title'))
                        ->columnSpan(2)
                        ->columns(2)
                        ->schema([
                            self::setCreatedAt(),
                            self::setCreatedBy(),
                            self::setMethod(),
                            self::setAction(),
                            self::setPath()->columnSpanFull(),
                            self::setIpv4(),
                            self::setIpv6(),
                        ]),
                    Section::make(__('models.timeline.fields.resource'))
                        ->columnSpan(1)
                        ->columns(1)
                        ->schema([
                            self::setResourceType(),
                            self::setResourceId(),
                        ]),
                ]),
        ];
    }
}
