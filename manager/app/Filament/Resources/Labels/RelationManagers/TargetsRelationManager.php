<?php

namespace App\Filament\Resources\Labels\RelationManagers;

use App\Filament\Components\Target\TargetForm;
use App\Filament\Components\Target\TargetTable;
use App\Traits\Filament\Specifics\Target\TargetButton;
use App\Traits\Filament\Specifics\Target\TargetData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TargetsRelationManager extends RelationManager
{
    use TargetButton, TargetData;

    protected static string $relationship = 'targets';

    public function form(Schema $schema): Schema
    {
        return $schema->components(TargetForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(TargetTable::build())
            ->filters([
                //
            ])
            ->headerActions([
                self::attachButton(),
            ])
            ->recordActions([
                self::buttonGroup(delete: false, more: [self::detachButton()]),
            ])
            ->toolbarActions([
                self::bulkButtonGroup(false, [self::detachBulkButton()]),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('models.target.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.target.name');
    }
}
