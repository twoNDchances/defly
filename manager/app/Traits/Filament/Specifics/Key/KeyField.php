<?php

namespace App\Traits\Filament\Specifics\Key;

use App\Traits\Filament\Generals\Components\Field;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

trait KeyField
{
    use Field, KeyButton, KeyData;

    public static function setName()
    {
        return self::textInput('name', __('models.key.fields.name'))
            ->helperText(__('forms.key.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required()
            ->rules(fn ($record) => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('keys', 'name')->ignore($record?->getKey()),
            ]);
    }

    public static function setToken()
    {
        return self::textInput('token', __('models.key.fields.token'))
            ->helperText(__('forms.key.descriptions.token'))
            ->default(fn ($operation) => $operation === 'create' ? Str::random(64) : null)
            ->copyable()
            ->password()
            ->revealable()
            ->suffixActions(
                [
                    self::generateTokenButton(),
                ]
            )
            ->required(fn ($operation) => $operation === 'create')
            ->rules(fn ($record, $operation) => [
                $operation === 'create' ? 'required' : 'nullable',
                'string',
                'min:16',
                'max:255',
                Rule::unique('keys', 'token')->ignore($record?->getKey()),
            ]);
    }

    public static function setExpiredAt()
    {
        return self::datetimePicker('expired_at', __('models.key.fields.expired_at'))
            ->helperText(__('forms.key.descriptions.expired_at'))
            ->seconds(false)
            ->nullable()
            ->rules(['nullable', 'date']);
    }

    public static function setIsReused()
    {
        return self::toggle('is_reused', __('models.key.fields.is_reused'))
            ->helperText(__('forms.key.descriptions.is_reused'))
            ->default(false)
            ->required()
            ->rules(['required', 'boolean']);
    }

    public static function setDescriptionField()
    {
        return self::setDescription();
    }
}
