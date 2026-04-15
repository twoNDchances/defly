<?php

namespace App\Filament\Components\Wordlist;

use App\Models\Wordlist;
use App\Traits\Filament\Specifics\Wordlist\WordlistButton;
use App\Traits\Filament\Specifics\Wordlist\WordlistData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class WordlistRelationManager extends RelationManager
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
                self::createButton(),
                self::attachButton()->after(function (array $data): void {
                    $recordIds = Arr::wrap($data['recordId'] ?? []);

                    if ($recordIds !== []) {
                        Wordlist::query()->whereKey($recordIds)->update(['locked' => true]);
                    }
                }),
            ])
            ->recordActions([
                self::buttonGroup(delete: false, more: [
                    self::detachButton()->after(function (Wordlist $record): void {
                        $record->update(['locked' => $record->targets()->exists() || $record->labels()->exists()]);
                    }),
                ]),
            ])
            ->toolbarActions([
                self::bulkButtonGroup(false, [
                    self::detachBulkButton()->after(function (Collection $records): void {
                        $records->each(function (Wordlist $record): void {
                            $record->update(['locked' => $record->targets()->exists() || $record->labels()->exists()]);
                        });
                    }),
                ]),
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
