<?php

namespace App\Filament\Resources\Labels\RelationManagers;

use App\Filament\Components\Guard\GuardForm;
use App\Filament\Components\Guard\GuardTable;
use App\Traits\Filament\Specifics\Guard\GuardButton;
use App\Traits\Filament\Specifics\Guard\GuardData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class GuardsRelationManager extends RelationManager
{
    use GuardButton, GuardData;

    protected static string $relationship = 'guards';

    public function form(Schema $schema): Schema
    {
        return $schema->components(GuardForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(GuardTable::build())
            ->filters([
                //
            ])
            ->headerActions([
                self::attachButton(),
            ])
            ->recordActions([
                self::detachButton(),
            ])
            ->toolbarActions([
                self::bulkButtonGroup(false, [self::detachBulkButton()]),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('models.guard.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.guard.name');
    }
}
