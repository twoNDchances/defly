<?php

namespace App\Filament\Components\Defender;

use App\Enums\Defender\DeploymentStatus;
use App\Traits\Filament\Specifics\Defender\DefenderField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class DefenderForm
{
    use DefenderField;

    public static function build()
    {
        $resolveDeploymentStatus = static function ($record): ?DeploymentStatus {
            $status = $record?->deployment_status;

            if ($status instanceof DeploymentStatus) {
                return $status;
            }

            if (is_string($status)) {
                return DeploymentStatus::tryFrom($status);
            }

            return null;
        };

        $isDeploymentSuccessfulOnViewOrEdit = static function ($operation, $record) use ($resolveDeploymentStatus): bool {
            if (! in_array($operation, ['view', 'edit'], true)) {
                return false;
            }

            return $resolveDeploymentStatus($record) === DeploymentStatus::Successful;
        };

        $isEditLockedBySuccessfulDeployment = static function ($operation, $record) use ($resolveDeploymentStatus): bool {
            if ($operation !== 'edit') {
                return false;
            }

            return $resolveDeploymentStatus($record) === DeploymentStatus::Successful;
        };

        $isDeploymentFailedOnViewOrEdit = static function ($operation, $record) use ($resolveDeploymentStatus): bool {
            if (! in_array($operation, ['view', 'edit'], true)) {
                return false;
            }

            return $resolveDeploymentStatus($record) === DeploymentStatus::Failed;
        };

        $shouldShowDeploymentStatusFields = static function ($operation, $record) use ($isDeploymentFailedOnViewOrEdit, $isDeploymentSuccessfulOnViewOrEdit): bool {
            return $isDeploymentFailedOnViewOrEdit($operation, $record)
                || $isDeploymentSuccessfulOnViewOrEdit($operation, $record);
        };

        $shouldShowRuntimeStatusFields = static function ($operation, $record) use ($isDeploymentSuccessfulOnViewOrEdit): bool {
            if (! in_array($operation, ['view', 'edit'], true)) {
                return false;
            }

            return $isDeploymentSuccessfulOnViewOrEdit($operation, $record);
        };

        return [
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    Tabs::make()
                        ->columnSpanFull()
                        ->contained(false)
                        ->schema([
                            Tab::make(__('forms.defender.tabs.a.title'))
                                ->columns(3)
                                ->schema([
                                    Section::make(__('forms.defender.tabs.a.sections.a.title'))
                                        ->collapsible($isDeploymentSuccessfulOnViewOrEdit)
                                        ->collapsed($isDeploymentSuccessfulOnViewOrEdit)
                                        ->columnSpan(2)
                                        ->columns(2)
                                        ->schema([
                                            self::setName()
                                                ->disabled($isEditLockedBySuccessfulDeployment)
                                                ->readOnly($isEditLockedBySuccessfulDeployment)
                                                ->required(fn ($operation, $record): bool => ! $isEditLockedBySuccessfulDeployment($operation, $record))
                                                ->validatedWhenNotDehydrated(fn ($operation, $record): bool => ! $isEditLockedBySuccessfulDeployment($operation, $record)),
                                            self::setProxyPort()
                                                ->disabled($isEditLockedBySuccessfulDeployment)
                                                ->readOnly($isEditLockedBySuccessfulDeployment)
                                                ->required(fn ($operation, $record): bool => ! $isEditLockedBySuccessfulDeployment($operation, $record))
                                                ->validatedWhenNotDehydrated(fn ($operation, $record): bool => ! $isEditLockedBySuccessfulDeployment($operation, $record)),
                                            self::setDescription()
                                                ->disabled($isEditLockedBySuccessfulDeployment)
                                                ->readOnly($isEditLockedBySuccessfulDeployment)
                                                ->validatedWhenNotDehydrated(fn ($operation, $record): bool => ! $isEditLockedBySuccessfulDeployment($operation, $record))
                                                ->columnSpanFull(),
                                        ]),

                                    Section::make(__('forms.generals.bases.sections.labels.title'))
                                        ->collapsible($isDeploymentSuccessfulOnViewOrEdit)
                                        ->collapsed($isDeploymentSuccessfulOnViewOrEdit)
                                        ->columnSpan(1)
                                        ->columns(1)
                                        ->schema([
                                            self::setLabels()
                                                ->disabled($isEditLockedBySuccessfulDeployment)
                                                ->validatedWhenNotDehydrated(fn ($operation, $record): bool => ! $isEditLockedBySuccessfulDeployment($operation, $record)),
                                        ]),
                                ]),

                            Tab::make(__('forms.defender.tabs.b.title'))
                                ->columns(3)
                                ->schema([
                                    Section::make(__('forms.defender.tabs.b.sections.a.title'))
                                        ->collapsed()
                                        ->columnSpan(1)
                                        ->columns(1)
                                        ->schema([
                                            self::setCommonEnvironmentVariables()
                                                ->disabled($isEditLockedBySuccessfulDeployment)
                                                ->validatedWhenNotDehydrated(fn ($operation, $record): bool => ! $isEditLockedBySuccessfulDeployment($operation, $record)),
                                        ]),
                                    Section::make(__('forms.defender.tabs.b.sections.b.title'))
                                        ->collapsed()
                                        ->columnSpan(1)
                                        ->columns(1)
                                        ->schema([
                                            self::setServerEnvironmentVariables()
                                                ->disabled($isEditLockedBySuccessfulDeployment)
                                                ->validatedWhenNotDehydrated(fn ($operation, $record): bool => ! $isEditLockedBySuccessfulDeployment($operation, $record)),
                                        ]),
                                    Section::make(__('forms.defender.tabs.b.sections.c.title'))
                                        ->collapsed()
                                        ->columnSpan(1)
                                        ->columns(1)
                                        ->schema([
                                            self::setProxyEnvironmentVariables()
                                                ->disabled($isEditLockedBySuccessfulDeployment)
                                                ->validatedWhenNotDehydrated(fn ($operation, $record): bool => ! $isEditLockedBySuccessfulDeployment($operation, $record)),
                                        ]),
                                ]),

                            Tab::make(__('forms.defender.tabs.c.title'))
                                ->visible($shouldShowDeploymentStatusFields)
                                ->columns(1)
                                ->schema([
                                    Section::make(__('forms.defender.tabs.c.sections.a.title'))
                                        ->collapsible()
                                        ->columns(1)
                                        ->visible($shouldShowRuntimeStatusFields)
                                        ->schema([
                                            self::setStatus(),
                                            self::setDetails(),
                                        ]),

                                    Section::make(__('forms.defender.tabs.c.sections.b.title'))
                                        ->collapsed($isDeploymentSuccessfulOnViewOrEdit)
                                        ->collapsible()
                                        ->columns(1)
                                        ->visible($shouldShowDeploymentStatusFields)
                                        ->schema([
                                            self::setDeploymentStatus(),
                                            self::setDeploymentDetails(),
                                        ]),
                                ]),

                            Tab::make(__('forms.defender.tabs.d.title'))
                                ->visible($isDeploymentSuccessfulOnViewOrEdit)
                                ->columns(1)
                                ->schema([
                                    Section::make(__('forms.defender.tabs.d.sections.a.title'))
                                        ->collapsible()
                                        ->columns(1)
                                        ->schema([
                                            self::setLog(),
                                            self::followDefenderButton(),
                                        ]),

                                    Section::make(__('forms.defender.tabs.d.sections.b.title'))
                                        ->collapsible()
                                        ->columns(1)
                                        ->schema([
                                            self::setLastResponseDetails(),
                                            self::refreshDefenderButton(),
                                        ]),
                                ]),
                        ]),
                ]),
        ];
    }
}
