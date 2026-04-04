<?php

namespace App\Filament\Resources\Labels\Tables;

use App\Traits\Filament\Specifics\Label\LabelButton;
use App\Traits\Filament\Specifics\Label\LabelColumn;
use Filament\Tables\Table;

class LabelsTable
{
    use LabelButton;
    use LabelColumn;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::columns())
            ->filters([
                //
            ])
            ->recordActions([
                self::buttonGroup(),
            ])
            ->toolbarActions([
                self::bulkButtonGroup(),
            ]);
    }

    public static function columns()
    {
        return [
            self::name(),
            self::color(),
            self::preview(),
        ];
    }
}
