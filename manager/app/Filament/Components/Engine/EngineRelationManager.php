<?php

namespace App\Filament\Components\Engine;

use App\Models\Engine;
use App\Models\Target;
use App\Traits\Filament\Specifics\Engine\EngineButton;
use App\Traits\Filament\Specifics\Engine\EngineData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EngineRelationManager extends RelationManager
{
    use EngineButton, EngineData;

    protected static string $relationship = 'engines';

    public function form(Schema $schema): Schema
    {
        return $schema->components(EngineForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(EngineTable::build())
            ->filters([
                //
            ])
            ->headerActions([
                self::createButton(),
                self::attachButton()->after(function (array $data, RelationManager $livewire): void {
                    $recordIds = Arr::wrap($data['recordId'] ?? []);

                    if ($recordIds !== []) {
                        Engine::query()->whereKey($recordIds)->update(['locked' => true]);
                    }

                    $ownerRecord = $livewire->getOwnerRecord();

                    if ($ownerRecord instanceof Target) {
                        $ownerRecord->update(['locked' => $ownerRecord->engines()->exists()]);
                    }
                }),
            ])
            ->recordActions([
                self::buttonGroup(delete: false, more: [
                    self::detachButton()->after(function (Engine $record, RelationManager $livewire): void {
                        $record->update(['locked' => $record->targets()->exists()]);

                        $ownerRecord = $livewire->getOwnerRecord();

                        if ($ownerRecord instanceof Target) {
                            $ownerRecord->update(['locked' => $ownerRecord->engines()->exists()]);
                        }
                    }),
                ]),
            ])
            ->toolbarActions([
                self::bulkButtonGroup(false, [
                    self::detachBulkButton()->after(function (Collection $records, RelationManager $livewire): void {
                        $records->each(function (Engine $record): void {
                            $record->update(['locked' => $record->targets()->exists()]);
                        });

                        $ownerRecord = $livewire->getOwnerRecord();

                        if ($ownerRecord instanceof Target) {
                            $ownerRecord->update(['locked' => $ownerRecord->engines()->exists()]);
                        }
                    }),
                ]),
            ])
            ->reorderable('order');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('models.engine.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.engine.name');
    }
}
