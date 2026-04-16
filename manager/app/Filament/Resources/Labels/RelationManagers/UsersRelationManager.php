<?php

namespace App\Filament\Resources\Labels\RelationManagers;

use App\Filament\Components\User\UserForm;
use App\Filament\Components\User\UserTable;
use App\Traits\Filament\Specifics\User\UserButton;
use App\Traits\Filament\Specifics\User\UserData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UsersRelationManager extends RelationManager
{
    use UserButton, UserData;

    protected static string $relationship = 'users';

    public function form(Schema $schema): Schema
    {
        return $schema->components(UserForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns(UserTable::build())
            ->filters([
                //
            ])
            ->headerActions([
                self::createButton(),
                self::attachButton()->recordSelectOptionsQuery(fn ($query) => $query->excludeCurrent()->excludeRoot()),
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
