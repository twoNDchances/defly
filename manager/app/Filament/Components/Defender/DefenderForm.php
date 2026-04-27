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

        $shouldShowDeploymentResultFields = static function ($operation, $record) use ($resolveDeploymentStatus): bool {
            if (! in_array($operation, ['view', 'edit'], true)) {
                return false;
            }

            return in_array(
                $resolveDeploymentStatus($record),
                [DeploymentStatus::Failed, DeploymentStatus::Successful],
                true,
            );
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
                                            self::setLabels(),
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
                        ]),
                    Section::make(__('forms.defender.sections.a.title'))
                        ->columns(1)
                        ->visible($shouldShowDeploymentResultFields)
                        ->schema([
                            self::setStatus(),
                            self::setDetails(),
                            self::setDeploymentStatus(),
                            self::setDeploymentDetails(),
                        ]),
                    Section::make(__('forms.defender.sections.b.title'))
                        ->columns(1)
                        ->visible($shouldShowDeploymentResultFields)
                        ->schema([
                            //
                        ]),
                ]),
        ];
    }
}
