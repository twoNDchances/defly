<?php

namespace App\Filament\Resources\Labels\RelationManagers;

use App\Filament\Components\Principle\PrincipleForm;
use App\Filament\Components\Principle\PrincipleTable;
use App\Traits\Filament\Specifics\Principle\PrincipleButton;
use App\Traits\Filament\Specifics\Principle\PrincipleData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PrinciplesRelationManager extends RelationManager
{
    use PrincipleButton, PrincipleData;

    protected static string $relationship = 'principles';

    public function form(Schema $schema): Schema
    {
        return $schema->components(PrincipleForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(PrincipleTable::build())
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
        return __('models.principle.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.principle.name');
    }
}
