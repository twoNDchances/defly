<?php

namespace App\Traits\Filament\Specifics\Decision;

use App\Enums\Action\Type as ActionType;
use App\Enums\Decision\Action;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Models\Action as ActionModel;

trait DecisionData
{
    public static function directionOptionsAndColors()
    {
        return [
            'options' => [
                Direction::Request->value => __('models.decision.extras.direction.request'),
                Direction::Response->value => __('models.decision.extras.direction.response'),
            ],
            'colors' => [
                Direction::Request->value => 'info',
                Direction::Response->value => 'danger',
            ],
        ];
    }

    public static function directionDescriptions()
    {
        return [
            null => __('forms.decision.descriptions.direction'),
            Direction::Request->value => __('forms.decision.extras.direction.request'),
            Direction::Response->value => __('forms.decision.extras.direction.response'),
        ];
    }

    public static function conditionOptionsAndColors()
    {
        return [
            'options' => [
                Condition::LessThan->value => __('models.decision.extras.condition.<'),
                Condition::LessThanOrEqual->value => __('models.decision.extras.condition.<='),
                Condition::Equal->value => __('models.decision.extras.condition.='),
                Condition::GreaterThanOrEqual->value => __('models.decision.extras.condition.>='),
                Condition::GreaterThan->value => __('models.decision.extras.condition.>'),
            ],
            'colors' => [
                Condition::LessThan->value => 'info',
                Condition::LessThanOrEqual->value => 'sky',
                Condition::Equal->value => 'gray',
                Condition::GreaterThanOrEqual->value => 'warning',
                Condition::GreaterThan->value => 'danger',
            ],
        ];
    }

    public static function conditionDescriptions()
    {
        return [
            null => __('forms.decision.descriptions.condition'),
            Condition::LessThan->value => __('forms.decision.extras.condition.<'),
            Condition::LessThanOrEqual->value => __('forms.decision.extras.condition.<='),
            Condition::Equal->value => __('forms.decision.extras.condition.='),
            Condition::GreaterThanOrEqual->value => __('forms.decision.extras.condition.>='),
            Condition::GreaterThan->value => __('forms.decision.extras.condition.>'),
        ];
    }

    public static function actionOptionsPerDirection()
    {
        return [
            null => null,
            Direction::Request->value => [
                Action::Allow->value => __('models.decision.extras.action.allow'),
                Action::Deny->value => __('models.decision.extras.action.deny'),
                Action::RewriteHeaders->value => __('models.decision.extras.action.rewrite_headers'),
                Action::RewriteBody->value => __('models.decision.extras.action.rewrite_body'),
                Action::Redirect->value => __('models.decision.extras.action.redirect'),
                Action::Cancel->value => __('models.decision.extras.action.cancel'),
                Action::Rewrite->value => __('models.decision.extras.action.rewrite'),
                Action::Save->value => __('models.decision.extras.action.save'),
            ],
            Direction::Response->value => [
                Action::Allow->value => __('models.decision.extras.action.allow'),
                Action::Deny->value => __('models.decision.extras.action.deny'),
                Action::RewriteHeaders->value => __('models.decision.extras.action.rewrite_headers'),
                Action::RewriteBody->value => __('models.decision.extras.action.rewrite_body'),
                Action::EraseCookies->value => __('models.decision.extras.action.erase_cookies'),
                Action::ForceNoCache->value => __('models.decision.extras.action.force_no_cache'),
            ],
        ];
    }

    public static function actionDescriptions()
    {
        return [
            null => __('forms.decision.descriptions.action'),
            Action::Allow->value => __('forms.decision.extras.action.allow'),
            Action::Deny->value => __('forms.decision.extras.action.deny'),
            Action::RewriteHeaders->value => __('forms.decision.extras.action.rewrite_headers'),
            Action::RewriteBody->value => __('forms.decision.extras.action.rewrite_body'),
            Action::Redirect->value => __('forms.decision.extras.action.redirect'),
            Action::Cancel->value => __('forms.decision.extras.action.cancel'),
            Action::Rewrite->value => __('forms.decision.extras.action.rewrite'),
            Action::Save->value => __('forms.decision.extras.action.save'),
            Action::EraseCookies->value => __('forms.decision.extras.action.erase_cookies'),
            Action::ForceNoCache->value => __('forms.decision.extras.action.force_no_cache'),
        ];
    }

    public static function denyDirectiveOptionsAndColors()
    {
        return [
            'options' => [
                'use_default' => __('models.decision.extras.deny_directive.use_default'),
                'copy_record' => __('models.decision.extras.deny_directive.copy_record'),
            ],
            'colors' => [
                'use_default' => 'purple',
                'copy_record' => 'secondary',
            ],
        ];
    }

    public static function rewriteDirectiveOptionsAndColors()
    {
        return [
            'options' => [
                'set' => __('models.decision.extras.directive.set'),
                'unset' => __('models.decision.extras.directive.unset'),
            ],
            'colors' => [
                'set' => 'info',
                'unset' => 'danger',
            ],
        ];
    }

    public static function rewriteTypeOptionsAndColors()
    {
        return [
            'options' => [
                'path' => __('models.decision.extras.rewrite_type.path'),
                'query' => __('models.decision.extras.rewrite_type.query'),
            ],
            'colors' => [
                'path' => 'info',
                'query' => 'warning',
            ],
        ];
    }

    public static function savePositionOptionsAndColors()
    {
        return [
            'options' => [
                'prefix' => __('models.decision.extras.save_position.prefix'),
                'suffix' => __('models.decision.extras.save_position.suffix'),
            ],
            'colors' => [
                'prefix' => 'info',
                'suffix' => 'warning',
            ],
        ];
    }

    public static function saveForm($data)
    {
        $rewriteHeadersDirective = $data['rewrite_headers_directive'] ?? null;
        $rewriteBodyDirective = $data['rewrite_body_directive'] ?? null;
        $rewriteType = $data['rewrite_type'] ?? null;
        $rewriteQueryDirective = $data['rewrite_query_directive'] ?? null;
        $denyRecordId = ($data['deny_directive'] ?? null) == 'copy_record' ? ($data['deny_record'] ?? null) : null;
        $denyConfigurations = [];

        if (filled($denyRecordId)) {
            $denyConfigurations = ActionModel::query()
                ->where('type', ActionType::Deny)
                ->find($denyRecordId)
                ?->configurations ?? [];

            if (! is_array($denyConfigurations)) {
                $denyConfigurations = [];
            }
        }

        $data['configurations'] = match ($data['action']) {
            Action::Deny->value => [
                'directive' => $data['deny_directive'] ?? null,
                'record' => $denyRecordId,
                'status' => $denyConfigurations['status'] ?? null,
                'content_type' => $denyConfigurations['content_type'] ?? null,
                'body' => $denyConfigurations['body'] ?? null,
            ],
            Action::RewriteHeaders->value => [
                'directive' => $rewriteHeadersDirective,
                'execution' => match ($rewriteHeadersDirective) {
                    'set' => $data['rewrite_headers_set'] ?? [],
                    'unset' => $data['rewrite_headers_unset'] ?? [],
                    default => [],
                },
            ],
            Action::RewriteBody->value => [
                'directive' => $rewriteBodyDirective,
                'execution' => match ($rewriteBodyDirective) {
                    'set' => $data['rewrite_body_set'] ?? [],
                    'unset' => $data['rewrite_body_unset'] ?? [],
                    default => [],
                },
            ],
            Action::Redirect->value => [
                'url' => $data['redirect_url'] ?? null,
            ],
            Action::Rewrite->value => [
                'type' => $rewriteType,
                'path' => $rewriteType == 'path' ? ($data['rewrite_path'] ?? null) : null,
                'query' => $rewriteType == 'query' ? [
                    'directive' => $rewriteQueryDirective,
                    'execution' => match ($rewriteQueryDirective) {
                        'set' => $data['rewrite_query_set'] ?? [],
                        'unset' => $data['rewrite_query_unset'] ?? [],
                        default => [],
                    },
                ] : null,
            ],
            Action::Save->value => [
                'position' => $data['save_position'] ?? null,
                'name' => $data['save_name'] ?? null,
            ],
            default => null,
        };

        return $data;
    }

    public static function loadForm($data)
    {
        $configurations = $data['configurations'] ?? [];

        if (! is_array($configurations)) {
            $configurations = [];
        }

        switch ($data['action'] ?? null) {
            case Action::Deny->value:
                $data['deny_directive'] = $configurations['directive'] ?? 'use_default';
                $data['deny_record'] = $configurations['record'] ?? null;
                break;
            case Action::RewriteHeaders->value:
                $directive = $configurations['directive'] ?? 'set';
                $execution = $configurations['execution'] ?? [];

                $data['rewrite_headers_directive'] = $directive;
                $data['rewrite_headers_set'] = $directive == 'set' ? $execution : [];
                $data['rewrite_headers_unset'] = $directive == 'unset' ? $execution : [];
                break;
            case Action::RewriteBody->value:
                $directive = $configurations['directive'] ?? 'set';
                $execution = $configurations['execution'] ?? [];

                $data['rewrite_body_directive'] = $directive;
                $data['rewrite_body_set'] = $directive == 'set' ? $execution : [];
                $data['rewrite_body_unset'] = $directive == 'unset' ? $execution : [];
                break;
            case Action::Redirect->value:
                $data['redirect_url'] = $configurations['url'] ?? null;
                break;
            case Action::Rewrite->value:
                $type = $configurations['type'] ?? 'path';
                $query = $configurations['query'] ?? [];

                if (! is_array($query)) {
                    $query = [];
                }

                $directive = $query['directive'] ?? 'set';
                $execution = $query['execution'] ?? [];

                $data['rewrite_type'] = $type;
                $data['rewrite_path'] = $type == 'path' ? ($configurations['path'] ?? null) : null;
                $data['rewrite_query_directive'] = $directive;
                $data['rewrite_query_set'] = $type == 'query' && $directive == 'set' ? $execution : [];
                $data['rewrite_query_unset'] = $type == 'query' && $directive == 'unset' ? $execution : [];
                break;
            case Action::Save->value:
                $data['save_position'] = $configurations['position'] ?? 'prefix';
                $data['save_name'] = $configurations['name'] ?? null;
                break;
            default:
                break;
        }

        return $data;
    }
}
