<?php

namespace App\Traits\Filament\Specifics\Principle;

use App\Enums\Phase;
use App\Traits\Filament\Generals\Components\Field;
use App\Traits\Validators\PrincipleValidator;
use Filament\Forms\Components\CodeEditor\Enums\Language;

trait PrincipleField
{
    use Field, PrincipleButton, PrincipleData, PrincipleValidator;

    public static function setName()
    {
        return self::textInput('name', __('models.principle.fields.name'), __('forms.principle.text_examples.name'))
            ->helperText(__('forms.principle.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required()
            ->rules(fn ($livewire) => self::validateName(ignore: $livewire->record ?? null));
    }

    public static function setLevel()
    {
        return self::textInput('level', __('models.principle.fields.level'), __('forms.principle.text_examples.level'))
            ->helperText(__('forms.principle.descriptions.level'))
            ->required()
            ->minValue(1)
            ->gt(0)
            ->default(1)
            ->rules(self::validateLevel())
            ->integer();
    }

    public static function setPhase()
    {
        return self::toggleButtons('phase', __('models.principle.fields.phase'), self::phaseOptionsAndColors())
            ->helperText(fn ($state) => self::phaseDescriptions()[$state])
            ->required()
            ->default(Phase::One->value)
            ->rules(self::validatePhase())
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
            ->rules(self::validateValidationStatus())
            ->disabled()
            ->visibleOn(['view', 'edit']);
    }

    public static function setValidationDetails()
    {
        return self::codeEditor('validation_details', __('models.principle.fields.validation_details'), Language::Json)
            ->helperText(__('forms.principle.descriptions.validation_details'))
            ->rules(self::validateValidationDetails())
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
