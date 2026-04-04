<?php

namespace App\Filament\Clusters\AccessControl\Resources\Permissions\RelationManagers;

use App\Filament\Clusters\Authentication\Resources\Users\Schemas\UserForm;
use App\Filament\Clusters\Authentication\Resources\Users\Tables\UsersTable;
use App\Traits\Filament\Specifics\User\UserButton;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UsersRelationManager extends RelationManager
{
    use UserButton;

    protected static string $relationship = 'users';

    public function form(Schema $schema): Schema
    {
        return $schema->components(UserForm::fields());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns(UsersTable::columns())
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
                self::bulkButtonGroup(false, [self::detachMultiUserButton()]),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('models.user.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.user.name');
    }
}
