<?php

namespace App\Traits\Filament\Generals\Components;

use App\Filament\Components\Label\LabelForm;
use App\Traits\Validators\GeneralValidator;
use Filament\Forms\Components;

trait Field
{
    use GeneralValidator;

    public static function textInput($name, $label = null, $placeholder = null, $rules = [])
    {
        return Components\TextInput::make($name)
            ->placeholder($placeholder)
            ->maxLength(255)
            ->label($label)
            ->rules($rules);
    }

    public static function textArea($name, $label = null, $placeholder = null, $rules = [])
    {
        return Components\Textarea::make($name)
            ->placeholder($placeholder)
            ->label($label)
            ->rows(6)
            ->rules($rules);
    }

    public static function toggle($name, $label = null, $rules = [])
    {
        return Components\Toggle::make($name)
            ->label($label)
            ->rules($rules);
    }

    public static function select($name, $label = null, $rules = [])
    {
        return Components\Select::make($name)
            ->label($label)
            ->searchable()
            ->preload()
            ->rules($rules);
    }

    public static function fileUpload($name, $label = null, $directory = null, $rules = [])
    {
        return Components\FileUpload::make($name)
            ->label($label)
            ->directory($directory)
            ->rules($rules);
    }

    public static function toggleButtons($name, $label = null, $colorsAndOptions = ['colors' => [], 'options' => []], $rules = [])
    {
        return Components\ToggleButtons::make($name)
            ->options($colorsAndOptions['options'])
            ->colors($colorsAndOptions['colors'])
            ->label($label)
            ->inline()
            ->rules($rules);
    }

    public static function colorPicker($name, $label = null, $rules = [])
    {
        return Components\ColorPicker::make($name)
            ->default('#000000')
            ->label($label)
            ->rules($rules);
    }

    public static function repeater($name, $label = null, $key = 'key', $schema = [])
    {
        return Components\Repeater::make($name)
            ->itemLabel(fn (array $state) => $state[$key] ?? null)
            ->schema($schema)
            ->label($label)
            ->columns(2)
            ->collapsible()
            ->cloneable();
    }

    public static function codeEditor($name, $label = null, $language = null)
    {
        return Components\CodeEditor::make($name)
            ->language($language)
            ->label($label);
    }

    public static function setDescription()
    {
        return self::textArea(
            'description',
            __('models.generals.bases.description'),
            __('forms.generals.bases.fields.description.text_examples'),
        )
            ->helperText(__('forms.generals.bases.fields.description.descriptions'));
    }

    public static function setLabels()
    {
        return self::select(
            'labels',
            __('models.label.name'),
            self::validateLabels(),
        )
            ->helperText(__('forms.generals.bases.sections.labels.description'))
            ->multiple()
            ->relationship('labels', 'name')
            ->createOptionForm(LabelForm::build());
    }
}
