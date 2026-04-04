<?php

namespace App\Filament\Clusters\AccessControl\Resources\Policies\RelationManagers;

use App\Filament\Clusters\AccessControl\Resources\Permissions\Schemas\PermissionForm;
use App\Filament\Clusters\AccessControl\Resources\Permissions\Tables\PermissionsTable;
use App\Traits\Filament\Specifics\Permission\PermissionButton;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PermissionsRelationManager extends RelationManager
{
    use PermissionButton;

    protected static string $relationship = 'permissions';

    public function form(Schema $schema): Schema
    {
        return $schema->components(PermissionForm::fields());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(PermissionsTable::columns())
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
        return __('models.permission.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.permission.name');
    }
}
