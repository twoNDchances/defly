<?php

namespace App\Traits\Filament\Specifics\Action;

use App\Enums\Action\Type;
use App\Enums\Method;
use App\Traits\Filament\Specifics\GeneralData;

trait ActionData
{
    use GeneralData;

    public static function typeOptionsAndColors()
    {
        return [
            'options' => [
                Type::Allow->value => __('models.action.extras.type.allow'),
                Type::Deny->value => __('models.action.extras.type.deny'),
                Type::Log->value => __('models.action.extras.type.log'),
                Type::Request->value => __('models.action.extras.type.request'),
                Type::Report->value => __('models.action.extras.type.report'),
                Type::Suspect->value => __('models.action.extras.type.suspect'),
                Type::Setter->value => __('models.action.extras.type.setter'),
                Type::Score->value => __('models.action.extras.type.score'),
                Type::Level->value => __('models.action.extras.type.level'),
            ],
            'colors' => [
                Type::Allow->value => 'success',
                Type::Deny->value => 'danger',
                Type::Log->value => 'info',
                Type::Request->value => 'warning',
                Type::Report->value => 'primary',
                Type::Suspect->value => 'rose',
                Type::Setter->value => 'gray',
                Type::Score->value => 'teal',
                Type::Level->value => 'orange',
            ],
        ];
    }

    public static function typeDescriptions()
    {
        return [
            null => __('forms.action.descriptions.type'),
            Type::Allow->value => __('forms.action.extras.type.allow'),
            Type::Deny->value => __('forms.action.extras.type.deny'),
            Type::Log->value => __('forms.action.extras.type.log'),
            Type::Request->value => __('forms.action.extras.type.request'),
            Type::Report->value => __('forms.action.extras.type.report'),
            Type::Suspect->value => __('forms.action.extras.type.suspect'),
            Type::Setter->value => __('forms.action.extras.type.setter'),
            Type::Score->value => __('forms.action.extras.type.score'),
            Type::Level->value => __('forms.action.extras.type.level'),
        ];
    }

    public static function denyContentTypeOptionsAndColors()
    {
        return [
            'options' => [
                'html' => 'HTML',
                'json' => 'JSON',
            ],
            'colors' => [
                'html' => 'orange',
                'json' => 'warning',
            ],
        ];
    }

    public static function denyContentTypeDescriptions()
    {
        return [
            null => __('forms.action.extras.configurations.deny_content_type'),
            'html' => __('forms.action.extras.deny_content_type.html'),
            'json' => __('forms.action.extras.deny_content_type.json'),
        ];
    }

    public static function requestMethodDescriptions()
    {
        return [
            null => __('forms.action.extras.configurations.request_method'),
            Method::Get->value => __('forms.generals.specials.method.get'),
            Method::Post->value => __('forms.generals.specials.method.post'),
            Method::Put->value => __('forms.generals.specials.method.put'),
            Method::Patch->value => __('forms.generals.specials.method.patch'),
            Method::Delete->value => __('forms.generals.specials.method.delete'),
        ];
    }

    public static function suspectSeverityOptionsAndColors()
    {
        return [
            'options' => [
                'info' => 'INFO',
                'notice' => 'NOTICE',
                'warning' => 'WARNING',
                'error' => 'ERROR',
                'critical' => 'CRITICAL',
                'alert' => 'ALERT',
                'emergency' => 'EMERGENCY',
            ],
            'colors' => [
                'info' => 'info',
                'notice' => 'success',
                'warning' => 'warning',
                'error' => 'danger',
                'critical' => 'rose',
                'alert' => 'pink',
                'emergency' => 'purple',
            ],
        ];
    }

    public static function setterDirectiveOptionsAndColors()
    {
        return [
            'options' => [
                'set' => __('models.action.extras.set'),
                'unset' => __('models.action.extras.unset'),
            ],
            'colors' => [
                'set' => 'info',
                'unset' => 'danger',
            ],
        ];
    }

    public static function setterDirectiveDescriptions()
    {
        return [
            null => __('forms.action.extras.configurations.setter_directive'),
            'set' => __('forms.action.extras.set'),
            'unset' => __('forms.action.extras.unset'),
        ];
    }

    public static function setterDatatypeOptionsAndColors()
    {
        return [
            'options' => [
                'number' => __('models.generals.specials.datatype.number'),
                'string' => __('models.generals.specials.datatype.string'),
            ],
            'colors' => [
                'number' => 'success',
                'string' => 'info',
            ],
        ];
    }

    public static function setterDatatypeDescriptions()
    {
        return [
            null => __('forms.action.extras.configurations.setter_set'),
            'number' => __('forms.generals.specials.datatype.number'),
            'string' => __('forms.generals.specials.datatype.string'),
        ];
    }

    public static function scoreBehaviorOptionsAndColors()
    {
        return [
            'options' => [
                'override' => __('models.action.extras.score_behavior.override'),
                '+' => __('models.action.extras.score_behavior.+'),
                '-' => __('models.action.extras.score_behavior.-'),
                '*' => __('models.action.extras.score_behavior.*'),
                '/' => __('models.action.extras.score_behavior./'),
            ],
            'colors' => [
                'override' => 'primary',
                '+' => 'success',
                '-' => 'danger',
                '*' => 'warning',
                '/' => 'info',
            ],
        ];
    }

    public static function scoreBehaviorDescriptions()
    {
        return [
            null => __('forms.action.extras.configurations.score_behavior'),
            'override' => __('forms.action.extras.score_behavior.override'),
            '+' => __('forms.action.extras.score_behavior.+'),
            '-' => __('forms.action.extras.score_behavior.-'),
            '*' => __('forms.action.extras.score_behavior.*'),
            '/' => __('forms.action.extras.score_behavior./'),
        ];
    }

    public static function levelBehaviorOptionsAndColors()
    {
        return [
            'options' => [
                'override' => __('models.action.extras.level_behavior.override'),
                'increase' => __('models.action.extras.level_behavior.increase'),
                'decrease' => __('models.action.extras.level_behavior.decrease'),
            ],
            'colors' => [
                'override' => 'primary',
                'increase' => 'success',
                'decrease' => 'danger',
            ],
        ];
    }

    public static function levelBehaviorDescriptions()
    {
        return [
            null => __('forms.action.extras.configurations.level_behavior'),
            'override' => __('forms.action.extras.level_behavior.override'),
            'increase' => __('forms.action.extras.level_behavior.increase'),
            'decrease' => __('forms.action.extras.level_behavior.decrease'),
        ];
    }

    public static function saveForm($data)
    {
        $directive = $data['setter_directive'] ?? null;

        $data['configurations'] = match ($data['type']) {
            Type::Deny->value => [
                'status' => $data['deny_status'] ?? null,
                'content_type' => $data['deny_content_type'] ?? null,
                'body' => $data['deny_body'] ?? null,
            ],
            Type::Log->value => [
                'format' => $data['log_format'] ?? null,
                'console' => $data['log_console'] ?? false,
                'file' => $data['log_file'] ?? false,
            ],
            Type::Request->value => [
                'url' => $data['request_url'] ?? null,
                'method' => $data['request_method'] ?? Method::Get->value,
                'headers' => $data['request_headers'] ?? [],
                'body' => $data['request_body'] ?? null,
            ],
            Type::Suspect->value => [
                'severity' => $data['suspect_severity'] ?? null,
            ],
            Type::Setter->value => [
                'directive' => $directive,
                'execution' => match ($directive) {
                    'set' => $data['setter_set'] ?? [],
                    'unset' => $data['setter_unset'] ?? [],
                    default => [],
                },
            ],
            Type::Score->value => [
                'operator' => $data['score_behavior'] ?? null,
                'value' => $data['score_value'] ?? null,
            ],
            Type::Level->value => [
                'operator' => $data['level_behavior'] ?? null,
                'value' => $data['level_value'] ?? null,
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

        switch ($data['type'] ?? null) {
            case Type::Deny->value:
                $data['deny_status'] = $configurations['status'] ?? null;
                $data['deny_content_type'] = $configurations['content_type'] ?? null;
                $data['deny_body'] = $configurations['body'] ?? null;
                break;
            case Type::Log->value:
                $data['log_format'] = $configurations['format'] ?? null;
                $data['log_console'] = $configurations['console'] ?? false;
                $data['log_file'] = $configurations['file'] ?? false;
                break;
            case Type::Request->value:
                $data['request_url'] = $configurations['url'] ?? null;
                $data['request_method'] = $configurations['method'] ?? Method::Get->value;
                $data['request_headers'] = $configurations['headers'] ?? [];
                $data['request_body'] = $configurations['body'] ?? null;
                break;
            case Type::Suspect->value:
                $data['suspect_severity'] = $configurations['severity'] ?? null;
                break;
            case Type::Setter->value:
                $directive = $configurations['directive'] ?? 'set';
                $execution = $configurations['execution'] ?? [];

                $data['setter_directive'] = $directive;
                $data['setter_set'] = $directive == 'set' ? $execution : [];
                $data['setter_unset'] = $directive == 'unset' ? $execution : [];
                break;
            case Type::Score->value:
                $data['score_behavior'] = $configurations['operator'] ?? null;
                $data['score_value'] = $configurations['value'] ?? null;
                break;
            case Type::Level->value:
                $data['level_behavior'] = $configurations['operator'] ?? null;
                $data['level_value'] = $configurations['value'] ?? null;
                break;
            default:
                break;
        }

        return $data;
    }
}
