<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Defenders\RelationManagers;

use App\Filament\Components\Report\ReportForm;
use App\Filament\Components\Report\ReportTable;
use App\Traits\Filament\Specifics\Report\ReportButton;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ReportsRelationManager extends RelationManager
{
    use ReportButton;

    protected static string $relationship = 'reports';

    public function form(Schema $schema): Schema
    {
        return $schema->components(ReportForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('created_at')
            ->columns(ReportTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                self::buttonGroup(edit: false, more: [
                    self::reviewReportButton(),
                ]),
            ])
            ->toolbarActions([
                self::bulkButtonGroup(more: [
                    self::reviewReportBulkButton(),
                ]),
            ])
            ->poll('5s');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('models.report.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.report.name');
    }
}
