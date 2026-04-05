<?php

namespace App\Filament\Components\Policy;

use App\Traits\Filament\Specifics\Policy\PolicyButton;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PolicyRelationManager extends RelationManager
{
    use PolicyButton;

    protected static string $relationship = 'policies';

    public function form(Schema $schema): Schema
    {
        return $schema->components(PolicyForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(PolicyTable::build())
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
        return __('models.policy.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.policy.name');
    }
}
