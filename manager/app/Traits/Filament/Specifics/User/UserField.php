<?php

namespace App\Traits\Filament\Specifics\User;

use App\Rules\User\RootField;
use App\Services\Identification;
use App\Traits\Filament\Generals\Components\Field;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

trait UserField
{
    use Field, UserButton, UserData;

    public static function setName()
    {
        return self::textInput('name', __('models.user.fields.name'), __('forms.user.text_examples.name'))
            ->helperText(__('forms.user.descriptions.name'))
            ->required();
    }

    public static function setEmail()
    {
        return self::textInput('email', __('models.user.fields.email'), __('forms.user.text_examples.email'))
            ->prefixIcon(Heroicon::OutlinedAtSymbol)
            ->helperText(__('forms.user.descriptions.email'))
            ->unique(ignoreRecord: true)
            ->required()
            ->email();
    }

    public static function setPassword()
    {
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
            ->required(fn ($livewire) => $livewire instanceof CreateRecord)
            ->password();
    }

    public static function setIsActivated()
    {
        return self::toggle('is_activated', __('models.user.fields.is_activated'))
            ->helperText(__('forms.user.descriptions.is_activated'))
            ->required()
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
            ->rule(new RootField)
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
            ->default(true);
    }
}
