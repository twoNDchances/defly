<?php

namespace App\Filament\Clusters\AccessControl\Resources\Policies\Tables;

use App\Models\Policy;
use App\Traits\Filament\Specifics\Policy\PolicyButton;
use App\Traits\Filament\Specifics\Policy\PolicyColumn;
use Filament\Tables\Table;

class PoliciesTable
{
    use PolicyButton;
    use PolicyColumn;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::columns())
            ->query(Policy::query()->manage())
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
            self::users(),
            self::permissions(),
            self::canManageFromOther(),
            self::createdBy(),
            self::createdAt(),
            self::updatedAt(),
        ];
    }
}
