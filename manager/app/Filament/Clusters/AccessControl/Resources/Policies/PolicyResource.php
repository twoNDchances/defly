<?php

namespace App\Filament\Clusters\AccessControl\Resources\Policies;

use App\Filament\Clusters\AccessControl\AccessControlCluster;
use App\Filament\Clusters\AccessControl\Resources\Policies\Pages\CreatePolicy;
use App\Filament\Clusters\AccessControl\Resources\Policies\Pages\EditPolicy;
use App\Filament\Clusters\AccessControl\Resources\Policies\Pages\ListPolicies;
use App\Filament\Clusters\AccessControl\Resources\Policies\Schemas\PolicyForm;
use App\Filament\Clusters\AccessControl\Resources\Policies\Tables\PoliciesTable;
use App\Models\Policy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PolicyResource extends Resource
{
    protected static ?string $model = Policy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $cluster = AccessControlCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PolicyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PoliciesTable::configure($table);
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
            'index' => ListPolicies::route('/'),
            'create' => CreatePolicy::route('/create'),
            'edit' => EditPolicy::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('models.policy.name');
    }
}
