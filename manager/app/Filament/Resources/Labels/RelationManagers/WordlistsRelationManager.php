<?php

namespace App\Filament\Resources\Labels\RelationManagers;

use App\Filament\Components\Wordlist\WordlistForm;
use App\Filament\Components\Wordlist\WordlistTable;
use App\Traits\Filament\Specifics\Wordlist\WordlistButton;
use App\Traits\Filament\Specifics\Wordlist\WordlistData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WordlistsRelationManager extends RelationManager
{
    use WordlistButton, WordlistData;

    protected static string $relationship = 'wordlists';

    public function form(Schema $schema): Schema
    {
        return $schema->components(WordlistForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(WordlistTable::build())
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
        return __('models.wordlist.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.wordlist.name');
    }
}
