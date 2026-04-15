<?php

namespace App\Filament\Components\Group;

use App\Traits\Filament\Specifics\Group\GroupButton;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class GroupRelationManager extends RelationManager
{
    use GroupButton;

    protected static string $relationship = 'groups';

    public function form(Schema $schema): Schema
    {
        return $schema->components(GroupForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(GroupTable::build())
            ->filters([
                //
            ])
            ->headerActions([
                self::createButton(),
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
        return __('models.group.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.group.name');
    }
}
