<?php

namespace App\Traits\Validators;

use App\Enums\Action\Type as ActionType;
use App\Enums\Decision\Action;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Rules\Decision\ActionField;
use Illuminate\Validation\Rule;

trait DecisionValidator
{
    private static function validateName($constraint = 'required', $ignore = null)
    {
        $unique = Rule::unique('decisions', 'name');

        if ($ignore) {
            $unique->ignore($ignore);
        }

        return [$constraint, 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $unique];
    }

    private static function validateDirection($constraint = 'required')
    {
        return [$constraint, Rule::enum(Direction::class)];
    }

    private static function validateCondition($constraint = 'required')
    {
        return [$constraint, Rule::enum(Condition::class)];
    }

    private static function validateScore($constraint = 'required')
    {
        return [$constraint, 'numeric', 'min:5'];
    }

    private static function validateAction($constraint = 'required')
    {
        return [$constraint, Rule::enum(Action::class), new ActionField];
    }

    private static function validateDenyDirective($constraint = 'required')
    {
        return [$constraint, 'nullable', 'string', Rule::in(['use_default', 'copy_record'])];
    }

    private static function validateRewriteDirective($constraint = 'required')
    {
        return [$constraint, 'nullable', 'string', Rule::in(['set', 'unset'])];
    }

    private static function validateDenyRecord($constraint = 'required_if:deny_directive,copy_record')
    {
        return [$constraint, 'nullable', Rule::exists('actions', 'id')->where('type', ActionType::Deny->value)];
    }

    private static function validateKeyValueItems($constraint = 'required')
    {
        return [$constraint, 'array'];
    }

    private static function validateKey($constraint = 'required')
    {
        return [$constraint, 'string', 'max:255'];
    }

    private static function validateValue($constraint = 'required')
    {
        return [$constraint, 'string'];
    }

    private static function validateRewriteType($constraint = 'required_if:action,rewrite')
    {
        return [$constraint, 'nullable', Rule::in(['path', 'query'])];
    }

    private static function validateRewritePath($constraint = 'required_if:rewrite_type,path')
    {
        return [$constraint, 'nullable', 'string', 'starts_with:/'];
    }

    private static function validateSavePosition($constraint = 'required_if:action,save')
    {
        return [$constraint, 'nullable', Rule::in(['prefix', 'suffix'])];
    }

    private static function validateSaveName($constraint = 'required_if:action,save')
    {
        return [$constraint, 'nullable', 'string', 'regex:/^[^\/\\\\:*?"<>|]+$/'];
    }

    private static function validateRedirectUrl($constraint = 'required_if:action,redirect')
    {
        return [$constraint, 'nullable', 'url'];
    }

    public static function validateDecision($ignore = null)
    {
        return [
            'name' => self::validateName(ignore: $ignore),
            'direction' => self::validateDirection(),
            'condition' => self::validateCondition(),
            'score' => self::validateScore(),
            'action' => self::validateAction(),
            'deny_directive' => self::validateDenyDirective('required_if:action,deny'),
            'deny_record' => self::validateDenyRecord(),
            'rewrite_headers_directive' => self::validateRewriteDirective('required_if:action,rewrite_headers'),
            'rewrite_headers_set' => self::validateKeyValueItems('required_if:rewrite_headers_directive,set'),
            'rewrite_headers_set.*.key' => self::validateKey(),
            'rewrite_headers_set.*.value' => self::validateValue(),
            'rewrite_headers_unset' => self::validateKeyValueItems('required_if:rewrite_headers_directive,unset'),
            'rewrite_headers_unset.*.key' => self::validateKey(),
            'rewrite_body_directive' => self::validateRewriteDirective('required_if:action,rewrite_body'),
            'rewrite_body_set' => self::validateKeyValueItems('required_if:rewrite_body_directive,set'),
            'rewrite_body_set.*.key' => self::validateKey(),
            'rewrite_body_set.*.value' => self::validateValue(),
            'rewrite_body_unset' => self::validateKeyValueItems('required_if:rewrite_body_directive,unset'),
            'rewrite_body_unset.*.key' => self::validateKey(),
            'rewrite_type' => self::validateRewriteType(),
            'rewrite_path' => self::validateRewritePath(),
            'rewrite_query_directive' => self::validateRewriteDirective('required_if:rewrite_type,query'),
            'rewrite_query_set' => self::validateKeyValueItems('required_if:rewrite_query_directive,set'),
            'rewrite_query_set.*.key' => self::validateKey(),
            'rewrite_query_set.*.value' => self::validateValue(),
            'rewrite_query_unset' => self::validateKeyValueItems('required_if:rewrite_query_directive,unset'),
            'rewrite_query_unset.*.key' => self::validateKey(),
            'save_position' => self::validateSavePosition(),
            'save_name' => self::validateSaveName(),
            'redirect_url' => self::validateRedirectUrl(),
            'description' => ['nullable'],
        ];
    }
}
