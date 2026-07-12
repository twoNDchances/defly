<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Guards\RelationManagers;

use App\Filament\Components\Defender\DefenderForm;
use App\Filament\Components\Defender\DefenderTable;
use App\Traits\Filament\Specifics\Defender\DefenderButton;
use App\Traits\Filament\Specifics\Defender\DefenderData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DefendersRelationManager extends RelationManager
{
    use DefenderButton, DefenderData;

    protected static string $relationship = 'defenders';

    public function form(Schema $schema): Schema
    {
        return $schema->components(DefenderForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(DefenderTable::build())
            ->filters([
                //
            ])
            ->headerActions([
                self::attachButton(),
            ])
            ->recordActions([
                self::detachButton(),
            ])
            ->toolbarActions([
                self::bulkButtonGroup(false, [self::detachBulkButton()]),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('models.defender.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.defender.name');
    }
}
