<?php

namespace App\Traits\Filament\Specifics\Engine;

use App\Enums\Datatype;
use App\Enums\Engine\Hash;
use App\Enums\Engine\Type;
use App\Traits\Filament\Generals\Components\Field;

trait EngineField
{
    use EngineButton, EngineData, Field;

    public static function setName()
    {
        return self::textInput(
            'name',
            __('models.engine.fields.name'),
            __('forms.engine.text_examples.name'),
        )
            ->helperText(__('forms.engine.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required();
    }

    public static function setInputDatatype()
    {
        return self::toggleButtons(
            'input_datatype',
            __('models.engine.fields.input_datatype'),
            EngineData::datatypeOptionsAndColors(),
        )
            ->helperText(fn ($state) => self::datatypeDescriptions()[$state])
            ->afterStateUpdated(fn ($set) => [$set('type', null), $set('output_datatype', null)])
            ->default(Datatype::Array->value)
            ->required()
            ->reactive();
    }

    public static function setType()
    {
        return self::select('type', __('models.engine.fields.type'))
            ->options(fn ($get) => self::typeOptionsPerDatatypes()[$get('input_datatype')])
            ->helperText(fn ($state, $get) => (
                ! $state ? __('forms.engine.descriptions.type')
                :
                self::typeDescriptionsPerDatatypes()[$get('input_datatype')][$state]
            ))
            ->afterStateUpdated(fn ($state, $set) => match ($state) {
                Type::Split->value => $set('output_datatype', Datatype::Array->value),
                Type::Addition->value,
                Type::Subtraction->value,
                Type::Multiplication->value,
                Type::Division->value,
                Type::PowerOf->value,
                Type::Remainder->value,
                Type::Length->value => $set('output_datatype', Datatype::Number->value),
                Type::IndexOf->value,
                Type::Lower->value,
                Type::Upper->value,
                Type::Capitalize->value,
                Type::Trim->value,
                Type::TrimLeft->value,
                Type::TrimRight->value,
                Type::RemoveWhitespace->value,
                Type::Hash->value,
                Type::Merge->value,
                Type::ToString->value => $set('output_datatype', Datatype::String->value),
            })
            ->required()
            ->selectablePlaceholder(false)
            ->reactive();
    }

    public static function setPosition()
    {
        $condition = fn ($get) => in_array($get('type'), [Type::IndexOf->value]);

        return self::textInput(
            'position',
            __('models.engine.extras.configurations.position'),
            '9947',
        )
            ->helperText(__('forms.engine.extras.configurations.position'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->integer();
    }

    public static function setDigit()
    {
        $condition = fn ($get) => in_array($get('type'), [
            Type::Addition->value,
            Type::Subtraction->value,
            Type::Multiplication->value,
            Type::Division->value,
            Type::PowerOf->value,
            Type::Remainder->value,
        ]);

        return self::textInput(
            'digit',
            __('models.engine.extras.configurations.digit'),
            '9948',
        )
            ->helperText(__('forms.engine.extras.configurations.digit'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->numeric();
    }

    public static function setHashMethod()
    {
        $condition = fn ($get) => in_array($get('type'), [Type::Hash->value]);

        return self::select(
            'hash_method',
            __('models.engine.extras.configurations.hash_method'),
        )
            ->helperText(__('forms.engine.extras.configurations.hash_method'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->options([
                Hash::Md5->value => 'MD5',
                Hash::Sha1->value => 'SHA1',
                Hash::Sha224->value => 'SHA224',
                Hash::Sha256->value => 'SHA256',
                Hash::Sha512->value => 'SHA512',
            ]);
    }

    public static function setSeparator()
    {
        $condition = fn ($get) => in_array($get('type'), [Type::Merge->value, Type::Split->value]);

        return self::textInput(
            'separator',
            __('models.engine.extras.configurations.separator'),
            '@',
        )
            ->helperText(__('forms.engine.extras.configurations.separator'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition);
    }

    public static function setOutputDatatype()
    {
        return self::toggleButtons(
            'output_datatype',
            __('models.engine.fields.output_datatype'),
            EngineData::datatypeOptionsAndColors(),
        )
            ->disabled();
    }
}
