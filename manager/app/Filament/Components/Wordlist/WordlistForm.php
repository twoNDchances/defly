<?php

namespace App\Filament\Components\Wordlist;

use App\Traits\Filament\Specifics\Wordlist\WordlistField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class WordlistForm
{
    use WordlistField;

    public static function build()
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Section::make(__('forms.wordlist.sections.a.title'))
                        ->columnSpan(2)
                        ->columns(2)
                        ->schema([
                            self::setName(),
                            self::setType(),
                            self::setDescriptionField()->columnSpanFull(),
                        ]),
                    Grid::make(1)
                        ->columnSpan(1)
                        ->schema([
                            Section::make(__('forms.wordlist.sections.b.title'))
                                ->columnSpanFull()
                                ->columns(1)
                                ->schema([
                                    self::setWordFile(),
                                    self::setWordJson(),
                                ]),
                            Section::make(__('forms.generals.bases.sections.labels.title'))
                                ->columnSpan(1)
                                ->columns(1)
                                ->schema([
                                    self::setLabels(),
                                ]),
                        ]),
                ]),
        ];
    }
}
