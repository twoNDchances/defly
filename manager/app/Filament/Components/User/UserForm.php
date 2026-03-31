<?php

namespace App\Filament\Components\User;

use App\Filament\Components\Form;
use App\Services\Identification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

trait UserForm
{
    use Form;
    use UserAction;

    public static function name()
    {
        return self::textInput('name', __('models.user.fields.name'), __('forms.user.text_examples.name'))
        ->helperText(__('forms.user.descriptions.name'))
        ->required();
    }

    public static function email()
    {
        return self::textInput('email', __('models.user.fields.email'), __('forms.user.text_examples.email'))
        ->prefixIcon(Heroicon::OutlinedAtSymbol)
        ->helperText(__('forms.user.descriptions.email'))
        ->unique(ignoreRecord: true)
        ->required()
        ->email();
    }

    public static function password()
    {
        return self::textInput('password', __('models.user.fields.password'), __('forms.user.text_examples.password'))
        ->helperText(__('forms.user.descriptions.password'))
        ->suffixActions(
            [
                self::generatePassword(),
                // self::copyPassword(),
            ],
        )
        ->minLength(4)
        ->revealable()
        ->required(fn ($livewire) => $livewire instanceof CreateRecord)
        ->password();
    }

    public static function isActivated()
    {
        return self::toggle('is_activated', __('models.user.fields.is_activated'))
        ->helperText(__('forms.user.descriptions.is_activated'))
        ->required()
        ->default(true);
    }

    public static function isRoot()
    {
        $condition = Identification::isRoot();
        return self::toggle('is_root', __('models.user.fields.is_root'))
        ->helperText(__('forms.user.descriptions.is_root'))
        ->required($condition)
        ->disabled(!$condition)
        ->visible($condition)
        ->rule(fn () => function ($attribute, $value, $fail) use ($condition) {
            if (! $condition) {
                $fail("The {$attribute} can only be used by authorized users.");
            }
        })
        ->default(false);
    }

    public static function isVerified()
    {
        $condition = fn($livewire) => $livewire instanceof CreateRecord;
        return self::toggle('is_verified', __('models.user.fields.is_verified'))
        ->helperText(__('forms.user.descriptions.is_verified'))
        ->required($condition)
        ->disabled(fn($livewire) => !$condition($livewire))
        ->visible($condition)
        ->default(true);
    }
}
