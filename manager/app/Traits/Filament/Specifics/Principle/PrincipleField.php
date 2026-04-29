<?php

namespace App\Traits\Filament\Specifics\Principle;

use App\Enums\Phase;
use App\Traits\Filament\Generals\Components\Field;
use Filament\Forms\Components\CodeEditor\Enums\Language;

trait PrincipleField
{
    use Field, PrincipleButton, PrincipleData;

    public static function setName()
    {
        return self::textInput('name', __('models.principle.fields.name'), __('forms.principle.text_examples.name'))
            ->helperText(__('forms.principle.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required();
    }

    public static function setLevel()
    {
        return self::textInput('level', __('models.principle.fields.level'), __('forms.principle.text_examples.level'))
            ->helperText(__('forms.principle.descriptions.level'))
            ->required()
            ->minValue(1)
            ->gt(0)
            ->default(1)
            ->integer();
    }

    public static function setPhase()
    {
        return self::toggleButtons('phase', __('models.principle.fields.phase'), self::phaseOptionsAndColors())
            ->helperText(fn ($state) => self::phaseDescriptions()[$state])
            ->required()
            ->default(Phase::One->value)
            ->reactive();
    }

    public static function setValidationStatus()
    {
        return self::toggleButtons(
            'validation_status',
            __('models.principle.fields.validation_status'),
            self::validationStatusOptionsAndColors(),
        )
            ->helperText(fn ($state) => self::validationStatusDescriptions()[$state])
            ->disabled()
            ->visibleOn(['view', 'edit']);
    }

    public static function setValidationDetails()
    {
        return self::codeEditor('validation_details', __('models.principle.fields.validation_details'), Language::Json)
            ->helperText(__('forms.principle.descriptions.validation_details'))
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
