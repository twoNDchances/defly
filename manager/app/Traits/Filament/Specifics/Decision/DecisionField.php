<?php

namespace App\Traits\Filament\Specifics\Decision;

use App\Enums\Action\Type;
use App\Enums\Decision\Action as ActionDecision;
use App\Models\Action as ActionModel;
use App\Traits\Filament\Generals\Components\Field;
use Filament\Support\Icons\Heroicon;

trait DecisionField
{
    use DecisionButton, DecisionData, Field;

    public static function setName()
    {
        return self::textInput(
            'name',
            __('models.decision.fields.name'),
            __('forms.decision.text_examples.name'),
        )
            ->helperText(__('forms.decision.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required();
    }

    public static function setDirection()
    {
        return self::toggleButtons(
            'direction',
            __('models.decision.fields.direction'),
            self::directionOptionsAndColors(),
        )
            ->helperText(fn ($state) => self::directionDescriptions()[$state])
            ->required()
            ->afterStateUpdated(fn ($set) => $set('action', null))
            ->reactive();
    }

    public static function setCondition()
    {
        return self::toggleButtons(
            'condition',
            __('models.decision.fields.condition'),
            self::conditionOptionsAndColors(),
        )
            ->helperText(fn ($state) => self::conditionDescriptions()[$state])
            ->required()
            ->reactive();
    }

    public static function setScore()
    {
        return self::textInput(
            'score',
            __('models.decision.fields.score'),
            __('forms.decision.text_examples.score'),
        )
            ->helperText(__('forms.decision.descriptions.score'))
            ->required()
            ->minValue(5)
            ->default(5)
            ->integer();
    }

    public static function setAction()
    {
        return self::select(
            'action',
            __('models.decision.fields.action'),
        )
            ->helperText(fn ($state) => self::actionDescriptions()[$state])
            ->options(fn ($get) => self::actionOptionsPerDirection()[$get('direction')])
            ->required()
            ->reactive();
    }

    public static function setDenyDirective()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::Deny->value;

        return self::toggleButtons(
            'deny_directive',
            __('models.decision.extras.configurations.deny_directive'),
            self::denyDirectiveOptionsAndColors(),
        )
            ->helperText(__('forms.decision.extras.configurations.deny_directive'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->reactive();
    }

    public static function setDenyRecord()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::Deny->value && $get('deny_directive') == 'copy_record';

        return self::select('deny_record', __('models.decision.extras.configurations.deny_record'))
            ->helperText(__('forms.decision.extras.configurations.deny_record'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->options(ActionModel::where('type', Type::Deny)->pluck('name', 'id')->toArray());
    }

    public static function setRewriteHeadersDirective()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::RewriteHeaders->value;

        return self::toggleButtons(
            'rewrite_headers_directive',
            __('models.decision.extras.configurations.rewrite_headers_directive'),
            self::rewriteDirectiveOptionsAndColors(),
        )
            ->helperText(__('forms.decision.extras.configurations.rewrite_headers_directive'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->reactive();
    }

    public static function setRewriteHeadersSet()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::RewriteHeaders->value && $get('rewrite_headers_directive') == 'set';

        return self::repeater(
            'rewrite_headers_set',
            __('models.decision.extras.configurations.rewrite_headers_set'),
            'key',
            [
                self::textInput(
                    'key',
                    __('models.decision.extras.key'),
                    'x-header-key',
                )
                    ->helperText(__('forms.decision.extras.key'))
                    ->alphaDash()
                    ->required(),

                self::textArea(
                    'value',
                    __('models.decision.extras.value'),
                    'header-value',
                )
                    ->helperText(__('forms.decision.extras.value'))
                    ->required(),
            ],
        )
            ->helperText(__('forms.decision.extras.configurations.rewrite_headers_set'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition);
    }

    public static function setRewriteHeadersUnset()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::RewriteHeaders->value && $get('rewrite_headers_directive') == 'unset';

        return self::repeater(
            'rewrite_headers_unset',
            __('models.decision.extras.configurations.rewrite_headers_unset'),
            'key',
            [
                self::textInput(
                    'key',
                    __('models.decision.extras.key'),
                    'x-header-key',
                )
                    ->helperText(__('forms.decision.extras.key'))
                    ->alphaDash()
                    ->required()
                    ->columnSpanFull(),
            ],
        )
            ->helperText(__('forms.decision.extras.configurations.rewrite_headers_unset'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition);
    }

    public static function setRewriteBodyDirective()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::RewriteBody->value;

        return self::toggleButtons(
            'rewrite_body_directive',
            __('models.decision.extras.configurations.rewrite_body_directive'),
            self::rewriteDirectiveOptionsAndColors(),
        )
            ->helperText(__('forms.decision.extras.configurations.rewrite_body_directive'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->reactive();
    }

    public static function setRewriteBodySet()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::RewriteBody->value && $get('rewrite_body_directive') == 'set';

        return self::repeater(
            'rewrite_body_set',
            __('models.decision.extras.configurations.rewrite_body_set'),
            'key',
            [
                self::textInput(
                    'key',
                    __('models.decision.extras.key'),
                    'body.key.name',
                )
                    ->helperText(__('forms.decision.extras.key'))
                    ->alphaDash()
                    ->required(),

                self::textArea(
                    'value',
                    __('models.decision.extras.value'),
                    'body value',
                )
                    ->helperText(__('forms.decision.extras.value'))
                    ->required(),
            ],
        )
            ->helperText(__('forms.decision.extras.configurations.rewrite_body_set'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition);
    }

    public static function setRewriteBodyUnset()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::RewriteBody->value && $get('rewrite_body_directive') == 'unset';

        return self::repeater(
            'rewrite_body_unset',
            __('models.decision.extras.configurations.rewrite_body_unset'),
            'key',
            [
                self::textInput(
                    'key',
                    __('models.decision.extras.key'),
                    'body.key.name',
                )
                    ->helperText(__('forms.decision.extras.key'))
                    ->alphaDash()
                    ->required()
                    ->columnSpanFull(),
            ],
        )
            ->helperText(__('forms.decision.extras.configurations.rewrite_body_unset'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition);
    }

    public static function setRewriteType()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::Rewrite->value;

        return self::toggleButtons(
            'rewrite_type',
            __('models.decision.extras.configurations.rewrite_type'),
            self::rewriteTypeOptionsAndColors(),
        )
            ->helperText(__('forms.decision.extras.configurations.rewrite_type'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->default('path')
            ->reactive();
    }

    public static function setRewritePath()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::Rewrite->value && $get('rewrite_type') == 'path';

        return self::textInput(
            'rewrite_path',
            __('models.decision.extras.configurations.rewrite_path'),
            '/path',
        )
            ->helperText(__('forms.decision.extras.configurations.rewrite_path'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition)
            ->prefixIcon(Heroicon::OutlinedLink)
            ->startsWith('/');
    }

    public static function setRewriteQueryDirective()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::Rewrite->value && $get('rewrite_type') == 'query';

        return self::toggleButtons(
            'rewrite_query_directive',
            __('models.decision.extras.configurations.rewrite_query_directive'),
            self::rewriteDirectiveOptionsAndColors(),
        )
            ->helperText(__('forms.decision.extras.configurations.rewrite_query_directive'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->default('set')
            ->reactive();
    }

    public static function setRewriteQuerySet()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::Rewrite->value
            && $get('rewrite_type') == 'query'
            && $get('rewrite_query_directive') == 'set';

        return self::repeater(
            'rewrite_query_set',
            __('models.decision.extras.configurations.rewrite_query_set'),
            'key',
            [
                self::textInput(
                    'key',
                    __('models.decision.extras.key'),
                    'query-key',
                )
                    ->helperText(__('forms.decision.extras.key'))
                    ->alphaDash()
                    ->required(),

                self::textArea(
                    'value',
                    __('models.decision.extras.value'),
                    'query-value',
                )
                    ->helperText(__('forms.decision.extras.value'))
                    ->required(),
            ],
        )
            ->helperText(__('forms.decision.extras.configurations.rewrite_query_set'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition);
    }

    public static function setRewriteQueryUnset()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::Rewrite->value
            && $get('rewrite_type') == 'query'
            && $get('rewrite_query_directive') == 'unset';

        return self::repeater(
            'rewrite_query_unset',
            __('models.decision.extras.configurations.rewrite_query_unset'),
            'key',
            [
                self::textInput(
                    'key',
                    __('models.decision.extras.key'),
                    'query-key',
                )
                    ->helperText(__('forms.decision.extras.key'))
                    ->alphaDash()
                    ->required()
                    ->columnSpanFull(),
            ],
        )
            ->helperText(__('forms.decision.extras.configurations.rewrite_query_unset'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition);
    }

    public static function setSavePosition()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::Save->value;

        return self::toggleButtons(
            'save_position',
            __('models.decision.extras.configurations.save_position'),
            self::savePositionOptionsAndColors(),
        )
            ->helperText(__('forms.decision.extras.configurations.save_position'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->default('prefix')
            ->reactive();
    }

    public static function setSaveName()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::Save->value;

        return self::textInput(
            'save_name',
            __('models.decision.extras.configurations.save_name'),
            'request.json',
        )
            ->helperText(__('forms.decision.extras.configurations.save_name'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition)
            ->regex('/^[^\/\\\\:*?"<>|]+$/');
    }

    public static function setRedirectUrl()
    {
        $condition = fn ($get) => $get('action') == ActionDecision::Redirect->value;

        return self::textInput(
            'redirect_url',
            __('models.decision.extras.configurations.redirect_url'),
            'http://alternative-backend.com',
        )
            ->helperText(__('forms.decision.extras.configurations.redirect_url'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition)
            ->prefixIcon(Heroicon::OutlinedServer)
            ->suffixAction(self::testRequestButton())
            ->url();
    }
}
