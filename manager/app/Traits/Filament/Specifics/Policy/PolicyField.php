<?php

namespace App\Traits\Filament\Specifics\Policy;

use App\Enums\Phase;
use App\Traits\Filament\Generals\Components\Field;
use Filament\Forms\Components\CodeEditor\Enums\Language;

trait PolicyField
{
    use Field, PolicyButton, PolicyData;

    public static function setName()
    {
        return self::textInput('name', __('models.policy.fields.name'), __('forms.policy.text_examples.name'))
            ->helperText(__('forms.policy.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required();
    }

    public static function setLevel()
    {
        return self::textInput('level', __('models.policy.fields.level'), __('forms.policy.text_examples.level'))
            ->helperText(__('forms.policy.descriptions.level'))
            ->required()
            ->minValue(1)
            ->gt(0)
            ->default(1)
            ->integer();
    }

    public static function setPhase()
    {
        return self::toggleButtons('phase', __('models.policy.fields.phase'), self::phaseOptionsAndColors())
            ->helperText(fn ($state) => self::phaseDescriptions()[$state])
            ->required()
            ->default(Phase::One->value)
            ->reactive();
    }

    public static function setValidationStatus()
    {
        return self::toggleButtons(
            'validation_status',
            __('models.policy.fields.validation_status'),
            self::validationStatusOptionsAndColors(),
        )
            ->helperText(fn ($state) => self::validationStatusDescriptions()[$state])
            ->disabled()
            ->visibleOn(['view', 'edit']);
    }

    public static function setValidationDetails()
    {
        return self::codeEditor('validation_details', __('models.policy.fields.validation_details'), Language::Json)
            ->formatStateUsing(function ($state) {
                if ($state === null) {
                    return null;
                }

                if (is_string($state)) {
                    return $state;
                }

                if (is_array($state)) {
                    return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                return (string) $state;
            })
            ->disabled()
            ->visibleOn(['view', 'edit']);
    }
}
