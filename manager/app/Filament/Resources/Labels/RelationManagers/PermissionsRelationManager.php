<?php

namespace App\Filament\Resources\Labels\RelationManagers;

use App\Filament\Components\Permission\PermissionForm;
use App\Filament\Components\Permission\PermissionTable;
use App\Traits\Filament\Specifics\Permission\PermissionButton;
use App\Traits\Filament\Specifics\Permission\PermissionData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PermissionsRelationManager extends RelationManager
{
    use PermissionButton, PermissionData;

    protected static string $relationship = 'permissions';

    public function form(Schema $schema): Schema
    {
        return $schema->components(PermissionForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(PermissionTable::build())
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
