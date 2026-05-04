<?php

namespace App\Traits\Filament\Specifics\User;

use App\Services\Identification;
use App\Traits\Filament\Generals\Components\Field;
use App\Traits\Validators\UserValidator;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

trait UserField
{
    use Field, UserButton, UserData, UserValidator;

    public static function setName()
    {
        return self::textInput(
            'name',
            __('models.user.fields.name'),
            __('forms.user.text_examples.name'),
        )
            ->helperText(__('forms.user.descriptions.name'))
            ->required()
            ->rules(self::validateName());
    }

    public static function setEmail()
    {
        return self::textInput('email', __('models.user.fields.email'), __('forms.user.text_examples.email'))
            ->prefixIcon(Heroicon::OutlinedAtSymbol)
            ->helperText(__('forms.user.descriptions.email'))
            ->unique(ignoreRecord: true)
            ->required()
            ->email()
            ->rules(fn ($record) => self::validateEmail(ignore: $record?->getKey()));
    }

    public static function setPassword()
    {
        $condition = fn ($livewire) => $livewire instanceof CreateRecord;

        return self::textInput('password', __('models.user.fields.password'), __('forms.user.text_examples.password'))
            ->helperText(__('forms.user.descriptions.password'))
            ->suffixActions(
                [
                    self::generatePasswordButton(),
                ],
            )
            ->copyable()
            ->minLength(4)
            ->revealable()
            ->required($condition)
            ->password()
            ->rules(fn ($livewire) => self::validatePassword($condition($livewire) ? 'required' : 'nullable'));
    }

    public static function setIsActivated()
    {
        return self::toggle('is_activated', __('models.user.fields.is_activated'))
            ->helperText(__('forms.user.descriptions.is_activated'))
            ->required()
            ->rules(self::validateIsActivated())
            ->default(true);
    }

    public static function setIsRoot()
    {
        $condition = Identification::isRoot();

        return self::toggle('is_root', __('models.user.fields.is_root'))
            ->helperText(__('forms.user.descriptions.is_root'))
            ->required($condition)
            ->disabled(! $condition)
            ->visible($condition)
            ->rules(self::validateIsRoot($condition ? 'required' : 'nullable'))
            ->default(false);
    }

    public static function setIsVerified()
    {
        $condition = fn ($livewire) => $livewire instanceof CreateRecord;

        return self::toggle('is_verified', __('models.user.fields.is_verified'))
            ->helperText(__('forms.user.descriptions.is_verified'))
            ->required($condition)
            ->disabled(fn ($livewire) => ! $condition($livewire))
            ->visible($condition)
            ->rules(fn ($livewire) => self::validateIsVerified($condition($livewire) ? 'required' : 'nullable'))
            ->default(true);
    }
}
