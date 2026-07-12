<?php

namespace App\Filament\Clusters\Context\Resources\Wordlists;

use App\Filament\Clusters\Context\ContextCluster;
use App\Filament\Clusters\Context\Resources\Wordlists\Pages\CreateWordlist;
use App\Filament\Clusters\Context\Resources\Wordlists\Pages\EditWordlist;
use App\Filament\Clusters\Context\Resources\Wordlists\Pages\ListWordlists;
use App\Filament\Clusters\Context\Resources\Wordlists\Schemas\WordlistForm;
use App\Filament\Clusters\Context\Resources\Wordlists\Tables\WordlistsTable;
use App\Models\Wordlist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WordlistResource extends Resource
{
    protected static ?string $model = Wordlist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $cluster = ContextCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return WordlistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WordlistsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWordlists::route('/'),
            'create' => CreateWordlist::route('/create'),
            'edit' => EditWordlist::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('models.wordlist.name');
    }
}
