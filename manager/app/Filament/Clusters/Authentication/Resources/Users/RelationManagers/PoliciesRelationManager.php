<?php

namespace App\Filament\Clusters\Authentication\Resources\Users\RelationManagers;

use App\Filament\Clusters\AccessControl\Resources\Policies\Schemas\PolicyForm;
use App\Filament\Clusters\AccessControl\Resources\Policies\Tables\PoliciesTable;
use App\Traits\Filament\Generals\Components\Button;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PoliciesRelationManager extends RelationManager
{
    use Button;

    protected static string $relationship = 'policies';

    protected static ?string $label = 'Chính sách';

    public function form(Schema $schema): Schema
    {
        return PolicyForm::configure($schema);
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
                EditAction::make(),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
