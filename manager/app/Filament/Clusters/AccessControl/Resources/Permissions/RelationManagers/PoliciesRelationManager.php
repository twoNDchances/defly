<?php

namespace App\Filament\Clusters\AccessControl\Resources\Permissions\RelationManagers;

use App\Filament\Clusters\AccessControl\Resources\Policies\Schemas\PolicyForm;
use App\Filament\Clusters\AccessControl\Resources\Policies\Tables\PoliciesTable;
use App\Traits\Filament\Specifics\Policy\PolicyButton;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PoliciesRelationManager extends RelationManager
{
    use PolicyButton;

    protected static string $relationship = 'policies';

    public function form(Schema $schema): Schema
    {
        return $schema->components(PolicyForm::fields());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(PoliciesTable::columns())
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
