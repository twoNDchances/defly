<?php

namespace App\Traits\Filament\Specifics\Policy;

use App\Enums\Policy\ValidationStatus;
use App\Traits\Filament\Specifics\GeneralData;

trait PolicyData
{
    use GeneralData;

    public static function validationStatusOptionsAndColors()
    {
        return [
            'options' => [
                ValidationStatus::Pending->value => __('models.policy.extras.validation_status.pending'),
                ValidationStatus::Validating->value => __('models.policy.extras.validation_status.validating'),
                ValidationStatus::Failed->value => __('models.policy.extras.validation_status.failed'),
                ValidationStatus::Passed->value => __('models.policy.extras.validation_status.passed'),
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
            null => __('forms.policy.descriptions.validation_status'),
            ValidationStatus::Pending->value => __('forms.policy.extras.validation_status.pending'),
            ValidationStatus::Validating->value => __('forms.policy.extras.validation_status.validating'),
            ValidationStatus::Failed->value => __('forms.policy.extras.validation_status.failed'),
            ValidationStatus::Passed->value => __('forms.policy.extras.validation_status.passed'),
        ];
    }
}
