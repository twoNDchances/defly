<?php

namespace App\Traits\Filament\Specifics\Action;

use App\Enums\Action\Type;
use App\Enums\Method;
use App\Traits\Filament\Generals\Components\Field;
use App\Traits\Validators\ActionValidator;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Schemas\Components\Grid;
use Symfony\Component\HttpFoundation\Response;

trait ActionField
{
    use ActionButton, ActionData, ActionValidator, Field;

    public static function setName()
    {
        return self::textInput(
            'name',
            __('models.action.fields.name'),
            __('forms.action.text_examples.name'),
        )
            ->helperText(__('forms.action.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required()
            ->rules(fn ($livewire) => self::validateName(ignore: $livewire->record ?? null));
    }

    public static function setType()
    {
        return self::toggleButtons(
            'type',
            __('models.action.fields.type'),
            self::typeOptionsAndColors(),
        )
            ->helperText(fn ($state) => self::typeDescriptions()[$state] ?? __('forms.action.descriptions.type'))
            ->required()
            ->rules(self::validateType())
            ->reactive()
            ->default(Type::Allow->value);
    }

    public static function setDenyStatus()
    {
        $condition = fn ($get) => $get('type') == Type::Deny->value;

        return self::select('deny_status', __('models.action.extras.configurations.deny_status'))
            ->helperText(__('forms.action.extras.configurations.deny_status'))
            ->disabled(fn ($get) => ! $condition($get))
            ->options(
                collect(Response::$statusTexts)
                    ->mapWithKeys(fn ($status, $code) => [$code => "[$code] $status"])
                    ->toArray()
            )
            ->required($condition)
            ->visible($condition)
            ->rules(fn ($get) => self::validateDenyStatus($condition($get) ? 'required' : 'nullable'))
            ->default(403);
    }

    public static function setDenyContentType()
    {
        $condition = fn ($get) => $get('type') == Type::Deny->value;

        return self::toggleButtons(
            'deny_content_type',
            __('models.action.extras.configurations.deny_content_type'),
            self::denyContentTypeOptionsAndColors(),
        )
            ->helperText(fn ($state) => self::denyContentTypeDescriptions()[$state] ?? __('forms.action.extras.configurations.deny_content_type'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition)
            ->default('json')
            ->rules(fn ($get) => self::validateDenyContentType($condition($get) ? 'required' : 'nullable'))
            ->afterStateUpdated(fn ($set) => $set('deny_body', null))
            ->reactive();
    }

    public static function setDenyBody()
    {
        $condition = fn ($get) => $get('type') == Type::Deny->value;

        return self::codeEditor(
            'deny_body',
            __('models.action.extras.configurations.deny_body'),
            fn ($get) => match ($get('deny_content_type')) {
                'html' => Language::Html,
                default => Language::Json,
            },
        )
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition)
            ->rules(fn ($get) => self::validateRequiredString($condition($get) ? 'required' : 'nullable'));
    }

    public static function setLogFormat()
    {
        $condition = fn ($get) => $get('type') == Type::Log->value;

        return self::textArea(
            'log_format',
            __('models.action.extras.configurations.log_format'),
            '%time% | %ip% | %method%',
        )
            ->helperText(__('forms.action.extras.configurations.log_format'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition)
            ->rules(fn ($get) => self::validateRequiredString($condition($get) ? 'required' : 'nullable'))
            ->default('[%time%] %ip% | %method% | %path% | %bytesSent% | %bytesReceived% | %error%');
    }

    public static function setLogConsole()
    {
        $condition = fn ($get) => $get('type') == Type::Log->value;

        return self::toggle(
            'log_console',
            __('models.action.extras.configurations.log_console'),
        )
            ->helperText(__('forms.action.extras.configurations.log_console'))
            ->default(true)
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition)
            ->rules(fn ($get) => self::validateRequiredBoolean($condition($get) ? 'required' : 'nullable'));
    }

    public static function setLogFile()
    {
        $condition = fn ($get) => $get('type') == Type::Log->value;

        return self::toggle(
            'log_file',
            __('models.action.extras.configurations.log_file'),
        )
            ->helperText(__('forms.action.extras.configurations.log_file'))
            ->default(true)
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition)
            ->rules(fn ($get) => self::validateRequiredBoolean($condition($get) ? 'required' : 'nullable'));
    }

    public static function setRequestUrl()
    {
        $condition = fn ($get) => $get('type') == Type::Request->value;

        return self::textInput(
            'request_url',
            __('models.action.extras.configurations.request_url'),
            'https://example.com',
        )
            ->helperText(__('forms.action.extras.configurations.request_url'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition)
            ->rules(fn ($get) => self::validateRequiredString($condition($get) ? 'required' : 'nullable'));
    }

    public static function setRequestMethod()
    {
        $condition = fn ($get) => $get('type') == Type::Request->value;

        return self::toggleButtons(
            'request_method',
            __('models.action.extras.configurations.request_method'),
            self::methodOptionsAndColors(),
        )
            ->helperText(fn ($state) => self::requestMethodDescriptions()[$state] ?? __('forms.action.extras.configurations.request_method'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition)
            ->default(Method::Get->value)
            ->rules(fn ($get) => self::validateRequestMethod($condition($get) ? 'required' : 'nullable'))
            ->reactive();
    }

    public static function setRequestHeaders()
    {
        $condition = fn ($get) => $get('type') == Type::Request->value;

        return self::repeater(
            'request_headers',
            __('models.action.extras.configurations.request_headers'),
            'key',
            [
                self::textInput('key', __('models.action.extras.key'), 'simple-header-key')
                    ->helperText(__('forms.action.extras.key'))
                    ->rules(self::validateKey())
                    ->required(),

                self::textArea('value', __('models.action.extras.value'), 'simple; header; value')
                    ->helperText(__('forms.action.extras.value'))
                    ->rules(self::validateRequiredString())
                    ->required(),
            ]
        )
            ->helperText(__('forms.action.extras.configurations.request_headers'))
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->rules(fn ($get) => self::validateRepeater($condition($get) ? 'nullable' : 'nullable'));
    }

    public static function setRequestBody()
    {
        $condition = fn ($get) => $get('type') == Type::Request->value;

        return self::codeEditor(
            'request_body',
            __('models.action.extras.configurations.request_body'),
            Language::Json,
        )
            ->helperText(__('forms.action.extras.configurations.request_body'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition)
            ->rules(fn ($get) => self::validateRequiredString($condition($get) ? 'required' : 'nullable'));
    }

    public static function setSuspectSeverity()
    {
        $condition = fn ($get) => $get('type') == Type::Suspect->value;

        return self::toggleButtons(
            'suspect_severity',
            __('models.action.extras.configurations.suspect_severity'),
            self::suspectSeverityOptionsAndColors(),
        )
            ->helperText(__('forms.action.extras.configurations.suspect_severity'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition)
            ->default('notice')
            ->rules(fn ($get) => self::validateRequiredString($condition($get) ? 'required' : 'nullable'));
    }

    public static function setSetterDirective()
    {
        $condition = fn ($get) => $get('type') == Type::Setter->value;

        return self::toggleButtons(
            'setter_directive',
            __('models.action.extras.configurations.setter_directive'),
            self::setterDirectiveOptionsAndColors(),
        )
            ->disabled(fn ($get) => ! $condition($get))
            ->helperText(fn ($state) => self::setterDirectiveDescriptions()[$state] ?? __('forms.action.extras.configurations.setter_directive'))
            ->required($condition)
            ->visible($condition)
            ->default('set')
            ->rules(fn ($get) => self::validateSetterDirective($condition($get) ? 'required' : 'nullable'))
            ->reactive();
    }

    public static function setSetterSet()
    {
        $condition = fn ($get) => $get('type') == Type::Setter->value && $get('setter_directive') == 'set';

        return self::repeater(
            'setter_set',
            __('models.action.extras.configurations.setter_set'),
            'key',
            [
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        self::textInput(
                            'key',
                            __('models.action.extras.key'),
                            'transaction-id',
                        )
                            ->helperText(__('forms.action.extras.key'))
                            ->alphaDash()
                            ->rules(self::validateKey())
                            ->required(),

                        self::toggleButtons(
                            'datatype',
                            __('models.target.fields.datatype'),
                            self::setterDatatypeOptionsAndColors(),
                        )
                            ->helperText(fn ($state) => self::setterDatatypeDescriptions()[$state] ?? __('forms.action.extras.configurations.setter_set'))
                            ->default('string')
                            ->reactive()
                            ->rules(self::validateSetterDatatype())
                            ->required(),
                    ]),

                self::textArea(
                    'value',
                    __('models.action.extras.value'),
                    'token-123',
                )
                    ->helperText(__('forms.action.extras.value'))
                    ->disabled(fn ($get) => $get('datatype') != 'string')
                    ->visible(fn ($get) => $get('datatype') == 'string')
                    ->required(fn ($get) => $get('datatype') == 'string')
                    ->rules(fn ($get) => self::validateRequiredString($get('datatype') == 'string' ? 'required' : 'nullable')),

                self::textInput(
                    'value',
                    __('models.action.extras.value'),
                    '10',
                )
                    ->helperText(__('forms.action.extras.value'))
                    ->disabled(fn ($get) => $get('datatype') != 'number')
                    ->visible(fn ($get) => $get('datatype') == 'number')
                    ->required(fn ($get) => $get('datatype') == 'number')
                    ->rules(fn ($get) => self::validatePositiveNumber($get('datatype') == 'number' ? 'required' : 'nullable'))
                    ->numeric(),
            ],
        )
            ->disabled(fn ($get) => ! $condition($get))
            ->helperText(__('forms.action.extras.configurations.setter_set'))
            ->required($condition)
            ->visible($condition)
            ->rules(fn ($get) => self::validateRepeater($condition($get) ? 'required' : 'nullable'));
    }

    public static function setSetterUnset()
    {
        $condition = fn ($get) => $get('type') == Type::Setter->value && $get('setter_directive') == 'unset';

        return self::repeater(
            'setter_unset',
            __('models.action.extras.configurations.setter_unset'),
            'key',
            [
                self::textInput(
                    'key',
                    __('models.action.extras.key'),
                    'transaction-id',
                )
                    ->helperText(__('forms.action.extras.key'))
                    ->rules(self::validateKey())
                    ->required()
                    ->columnSpanFull(),
            ],
        )
            ->disabled(fn ($get) => ! $condition($get))
            ->helperText(__('forms.action.extras.configurations.setter_unset'))
            ->required($condition)
            ->visible($condition)
            ->rules(fn ($get) => self::validateRepeater($condition($get) ? 'required' : 'nullable'));
    }

    public static function setScoreOperator()
    {
        $condition = fn ($get) => $get('type') == Type::Score->value;

        return self::toggleButtons(
            'score_behavior',
            __('models.action.extras.configurations.score_behavior'),
            self::scoreBehaviorOptionsAndColors(),
        )
            ->helperText(fn ($state) => self::scoreBehaviorDescriptions()[$state] ?? __('forms.action.extras.configurations.score_behavior'))
            ->nullable()
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->default('override')
            ->rules(fn ($get) => self::validateBehavior($condition($get) ? 'required' : 'nullable'));
    }

    public static function setScoreValue()
    {
        $condition = fn ($get) => $get('type') == Type::Score->value;

        return self::textInput(
            'score_value',
            __('models.action.extras.configurations.score_value'),
            '5',
        )
            ->disabled(fn ($get) => ! $condition($get))
            ->helperText(__('forms.action.extras.configurations.score_value'))
            ->required($condition)
            ->visible($condition)
            ->numeric()
            ->rules(fn ($get) => self::validatePositiveNumber($condition($get) ? 'required' : 'nullable'))
            ->minValue(1);
    }

    public static function setLevelOperator()
    {
        $condition = fn ($get) => $get('type') == Type::Level->value;

        return self::toggleButtons(
            'level_behavior',
            __('models.action.extras.configurations.level_behavior'),
            self::levelBehaviorOptionsAndColors(),
        )
            ->helperText(fn ($state) => self::levelBehaviorDescriptions()[$state] ?? __('forms.action.extras.configurations.level_behavior'))
            ->nullable()
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->default('override')
            ->rules(fn ($get) => self::validateBehavior($condition($get) ? 'required' : 'nullable'));
    }

    public static function setLevelValue()
    {
        $condition = fn ($get) => $get('type') == Type::Level->value;

        return self::textInput(
            'level_value',
            __('models.action.extras.configurations.level_value'),
            '1',
        )
            ->disabled(fn ($get) => ! $condition($get))
            ->helperText(__('forms.action.extras.configurations.level_value'))
            ->required($condition)
            ->visible($condition)
            ->numeric()
            ->rules(fn ($get) => self::validatePositiveNumber($condition($get) ? 'required' : 'nullable'))
            ->minValue(1);
    }
}
