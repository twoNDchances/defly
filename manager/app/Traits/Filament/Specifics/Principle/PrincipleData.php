<?php

namespace App\Traits\Filament\Specifics\Principle;

use App\Enums\Principle\ValidationStatus;
use App\Traits\Filament\Specifics\GeneralData;

trait PrincipleData
{
    use GeneralData;

    public static function validationStatusOptionsAndColors()
    {
        return [
            'options' => [
                ValidationStatus::Pending->value => __('models.principle.extras.validation_status.pending'),
                ValidationStatus::Validating->value => __('models.principle.extras.validation_status.validating'),
                ValidationStatus::Failed->value => __('models.principle.extras.validation_status.failed'),
                ValidationStatus::Passed->value => __('models.principle.extras.validation_status.passed'),
            ],
            'colors' => [
                ValidationStatus::Pending->value => 'secondary',
                ValidationStatus::Validating->value => 'info',
                ValidationStatus::Failed->value => 'danger',
                ValidationStatus::Passed->value => 'success',
            ],
        ];
    }

    public static function validationStatusDescriptions()
    {
        return [
            null => __('forms.principle.descriptions.validation_status'),
            ValidationStatus::Pending->value => __('forms.principle.extras.validation_status.pending'),
            ValidationStatus::Validating->value => __('forms.principle.extras.validation_status.validating'),
            ValidationStatus::Failed->value => __('forms.principle.extras.validation_status.failed'),
            ValidationStatus::Passed->value => __('forms.principle.extras.validation_status.passed'),
        ];
    }
}
