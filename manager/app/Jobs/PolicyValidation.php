<?php

namespace App\Jobs;

use App\Enums\Action\Type as ActionType;
use App\Enums\Datatype as DatatypeEnum;
use App\Enums\Method;
use App\Enums\Phase;
use App\Enums\Policy\ValidationStatus;
use App\Enums\Rule\Comparator;
use App\Enums\Type as TargetType;
use App\Enums\Wordlist\Type as WordlistType;
use App\Models\Action;
use App\Models\Policy;
use App\Models\Rule;
use App\Models\Target;
use App\Models\Wordlist;
use App\Services\Datatype as DatatypeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PolicyValidation implements ShouldQueue
{
    use Queueable;

    private const COMPARATORS_PER_DATATYPE = [
        DatatypeEnum::Array->value => [
            Comparator::Similar->value,
            Comparator::Contains->value,
            Comparator::Match->value,
            Comparator::Search->value,
        ],
        DatatypeEnum::Number->value => [
            Comparator::Equal->value,
            Comparator::GreaterThan->value,
            Comparator::LessThan->value,
            Comparator::GreaterThanOrEqual->value,
            Comparator::LessThanOrEqual->value,
            Comparator::InRange->value,
        ],
        DatatypeEnum::String->value => [
            Comparator::Mirror->value,
            Comparator::StartsWith->value,
            Comparator::EndsWith->value,
            Comparator::Check->value,
            Comparator::RegExp->value,
            Comparator::CheckRegExp->value,
        ],
    ];

    private const WORDLIST_REQUIRED_COMPARATORS = [
        Comparator::Similar->value,
        Comparator::Search->value,
        Comparator::Check->value,
        Comparator::CheckRegExp->value,
    ];

    private const SCORE_OPERATORS = ['override', '+', '-', '*', '/'];

    private const LEVEL_OPERATORS = ['override', 'increase', 'decrease'];

    private const SUSPECT_SEVERITIES = ['info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

    private const RULE_CONFIGURATION_SCHEMA = [
        Comparator::Contains->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'string' => ['type' => 'string'],
            ],
        ],
        Comparator::Match->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'string' => ['type' => 'string'],
            ],
        ],
        Comparator::Mirror->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'string' => ['type' => 'string'],
            ],
        ],
        Comparator::StartsWith->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'string' => ['type' => 'string'],
            ],
        ],
        Comparator::EndsWith->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'string' => ['type' => 'string'],
            ],
        ],
        Comparator::RegExp->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'string' => ['type' => 'string'],
            ],
        ],
        Comparator::Equal->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'number' => ['type' => 'numeric'],
            ],
        ],
        Comparator::GreaterThan->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'number' => ['type' => 'numeric'],
            ],
        ],
        Comparator::LessThan->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'number' => ['type' => 'numeric'],
            ],
        ],
        Comparator::GreaterThanOrEqual->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'number' => ['type' => 'numeric'],
            ],
        ],
        Comparator::LessThanOrEqual->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'number' => ['type' => 'numeric'],
            ],
        ],
        Comparator::InRange->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'number_from' => ['type' => 'numeric'],
                'number_to' => ['type' => 'numeric'],
            ],
        ],
        Comparator::Similar->value => null,
        Comparator::Search->value => null,
        Comparator::Check->value => null,
        Comparator::CheckRegExp->value => null,
    ];

    private const ACTION_CONFIGURATION_SCHEMA = [
        ActionType::Allow->value => null,
        ActionType::Report->value => null,
        ActionType::Deny->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'status' => ['type' => 'http_status'],
                'content_type' => ['type' => 'enum', 'values' => ['html', 'json']],
                'body' => ['type' => 'string'],
            ],
        ],
        ActionType::Log->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'format' => ['type' => 'string'],
                'console' => ['type' => 'bool'],
                'file' => ['type' => 'bool'],
            ],
        ],
        ActionType::Request->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'url' => ['type' => 'string'],
                'method' => ['type' => 'method'],
                'headers' => [
                    'type' => 'list',
                    'item' => [
                        'type' => 'shape',
                        'allow_extra' => false,
                        'fields' => [
                            'key' => ['type' => 'string'],
                            'value' => ['type' => 'string'],
                        ],
                    ],
                ],
                'body' => ['type' => 'string'],
            ],
        ],
        ActionType::Suspect->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'severity' => ['type' => 'enum', 'values' => self::SUSPECT_SEVERITIES],
            ],
        ],
        ActionType::Setter->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'directive' => ['type' => 'enum', 'values' => ['set', 'unset']],
                'execution' => [
                    'type' => 'list',
                    'item' => [
                        'type' => 'shape',
                        'allow_extra' => false,
                        'fields' => [
                            'key' => ['type' => 'string'],
                            'datatype' => [
                                'type' => 'enum',
                                'values' => [DatatypeEnum::Number->value, DatatypeEnum::String->value],
                                'required' => false,
                            ],
                            'value' => ['type' => 'any', 'required' => false],
                        ],
                    ],
                ],
            ],
        ],
        ActionType::Score->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'operator' => ['type' => 'enum', 'values' => self::SCORE_OPERATORS],
                'value' => ['type' => 'numeric', 'min' => 1],
            ],
        ],
        ActionType::Level->value => [
            'type' => 'shape',
            'allow_extra' => false,
            'fields' => [
                'operator' => ['type' => 'enum', 'values' => self::LEVEL_OPERATORS],
                'value' => ['type' => 'integer', 'min' => 1],
            ],
        ],
    ];

    public function __construct(public string $policyId) {}

    public function handle(): void
    {
        $policy = Policy::query()
            ->with([
                'rules' => fn ($query) => $query->with([
                    'target.pattern',
                    'target.wordlist',
                    'target.engines',
                    'wordlist',
                    'actions',
                ]),
            ])
            ->find($this->policyId);

        if (! $policy) {
            return;
        }

        $policy->forceFill([
            'validation_status' => ValidationStatus::Validating,
            'validation_details' => null,
        ])->save();

        try {
            $validation = $this->validatePolicy($policy);

            $policy->forceFill([
                'validation_status' => $validation['passed'] ? ValidationStatus::Passed : ValidationStatus::Failed,
                'validation_details' => $validation['details'],
            ])->save();
        } catch (Throwable $exception) {
            report($exception);

            $policy->forceFill([
                'validation_status' => ValidationStatus::Failed,
                'validation_details' => [
                    'status' => ValidationStatus::Failed->value,
                    'checked_at' => now()->toIso8601String(),
                    'errors' => [
                        [
                            'code' => 'policy.validation.exception',
                            'message' => 'Unhandled exception while validating policy.',
                            'context' => [
                                'exception' => $exception::class,
                                'message' => $exception->getMessage(),
                            ],
                        ],
                    ],
                ],
            ])->save();
        }
    }

    protected function validatePolicy(Policy $policy): array
    {
        $policyPhase = $this->normalizePhase($policy->getRawOriginal('phase'));

        $policyErrors = [];
        $ruleResults = [];
        $targetCache = [];

        if ($policyPhase === null) {
            $this->pushError($policyErrors, 'policy.phase.invalid', 'Policy phase is invalid.');
        }

        if ($policy->rules->isEmpty()) {
            $this->pushError($policyErrors, 'policy.rules.empty', 'Policy does not have any rules.');
        }

        foreach ($policy->rules as $rule) {
            $ruleResults[] = $this->validateRule($policy, $rule, $policyPhase, $targetCache);
        }

        $rulesFailed = 0;
        $targetsFailed = 0;
        $actionsFailed = 0;
        $errorsTotal = count($policyErrors);
        $targetErrorCounted = [];

        foreach ($ruleResults as $ruleResult) {
            if ($ruleResult['status'] === 'failed') {
                $rulesFailed++;
            }

            $errorsTotal += count($ruleResult['errors']);

            if (isset($ruleResult['target']) && is_array($ruleResult['target'])) {
                $targetKey = $ruleResult['target']['target']['id'] ?? md5(json_encode($ruleResult['target']['target'] ?? []));
                if (! array_key_exists($targetKey, $targetErrorCounted)) {
                    $targetErrorCounted[$targetKey] = true;
                    $errorsTotal += count($ruleResult['target']['errors']);
                    if ($ruleResult['target']['status'] === 'failed') {
                        $targetsFailed++;
                    }
                }
            }

            if (isset($ruleResult['wordlist']) && is_array($ruleResult['wordlist'])) {
                $errorsTotal += count($ruleResult['wordlist']['errors']);
            }

            foreach ($ruleResult['actions'] as $actionResult) {
                $errorsTotal += count($actionResult['errors']);
                if ($actionResult['status'] === 'failed') {
                    $actionsFailed++;
                }
            }
        }

        $passed = $errorsTotal === 0;

        return [
            'passed' => $passed,
            'details' => [
                'status' => $passed ? ValidationStatus::Passed->value : ValidationStatus::Failed->value,
                'checked_at' => now()->toIso8601String(),
                'policy' => [
                    'id' => $policy->id,
                    'name' => $policy->name,
                    'phase' => $policyPhase,
                ],
                'summary' => [
                    'rules_total' => $policy->rules->count(),
                    'rules_failed' => $rulesFailed,
                    'targets_failed' => $targetsFailed,
                    'actions_failed' => $actionsFailed,
                    'errors_total' => $errorsTotal,
                ],
                'errors' => $policyErrors,
                'rules' => $ruleResults,
            ],
        ];
    }

    protected function validateRule(Policy $policy, Rule $rule, ?int $policyPhase, array &$targetCache): array
    {
        $errors = [];
        $rulePhase = $this->normalizePhase($rule->getRawOriginal('phase'));
        $targetId = $rule->getRawOriginal('target_id');
        $comparator = $this->normalizeComparator($rule->getRawOriginal('comparator'));
        $wordlistId = $rule->getRawOriginal('wordlist_id');

        $result = [
            'rule' => [
                'id' => $rule->id,
                'name' => $rule->name,
                'phase' => $rulePhase,
                'target_id' => $targetId,
                'comparator' => $comparator,
                'is_inversed' => (bool) $rule->is_inversed,
                'wordlist_id' => $wordlistId,
            ],
            'errors' => [],
            'target' => null,
            'wordlist' => null,
            'actions' => [],
            'status' => 'passed',
        ];

        if ($rulePhase === null) {
            $this->pushError($errors, 'rule.phase.invalid', 'Rule phase is invalid.');
        }

        if (($rulePhase !== null) && ($policyPhase !== null) && ($rulePhase !== $policyPhase)) {
            $this->pushError(
                $errors,
                'rule.phase.policy_mismatch',
                'Rule phase does not match policy phase.',
                ['policy_phase' => $policyPhase, 'rule_phase' => $rulePhase, 'policy_id' => $policy->id]
            );
        }

        if (! filled($targetId)) {
            $this->pushError($errors, 'rule.target.required', 'Rule target_id is required.');
        }

        $target = $rule->target;
        $finalDatatype = null;

        if (filled($targetId) && (! $target)) {
            $this->pushError($errors, 'rule.target.missing', 'Rule target does not exist.', ['target_id' => $targetId]);
        }

        if ($target) {
            $targetKey = $target->id ?: ('target_'.spl_object_id($target));

            if (! array_key_exists($targetKey, $targetCache)) {
                $targetCache[$targetKey] = $this->validateTarget($target);
            }

            $targetResult = $targetCache[$targetKey];
            $result['target'] = $targetResult;
            $finalDatatype = $targetResult['final_datatype'];

            if ($targetResult['status'] === 'failed') {
                $this->pushError(
                    $errors,
                    'rule.target.invalid',
                    'Rule target failed target validations.',
                    ['target_id' => $target->id]
                );
            }

            $targetPhase = $this->normalizePhase($target->getRawOriginal('phase'));
            if (($rulePhase !== null) && ($targetPhase !== null) && ($rulePhase !== $targetPhase)) {
                $this->pushError(
                    $errors,
                    'rule.phase.target_mismatch',
                    'Rule phase does not match target phase.',
                    ['rule_phase' => $rulePhase, 'target_phase' => $targetPhase, 'target_id' => $target->id]
                );
            }
        }

        if ($comparator === null) {
            $this->pushError($errors, 'rule.comparator.invalid', 'Rule comparator is invalid.');
        } else {
            if (($finalDatatype !== null) && (! in_array($comparator, self::COMPARATORS_PER_DATATYPE[$finalDatatype] ?? [], true))) {
                $this->pushError(
                    $errors,
                    'rule.comparator.datatype_mismatch',
                    'Rule comparator does not match target final datatype.',
                    ['comparator' => $comparator, 'target_final_datatype' => $finalDatatype]
                );
            }

            $configurationValidation = $this->validateRuleConfigurations($comparator, $rule->configurations);
            if ($configurationValidation['status'] === 'failed') {
                foreach ($configurationValidation['errors'] as $configurationError) {
                    $errors[] = $configurationError;
                }
            }
        }

        $requiresWordlist = ($comparator !== null) && in_array($comparator, self::WORDLIST_REQUIRED_COMPARATORS, true);

        if ($requiresWordlist && (! filled($wordlistId))) {
            $this->pushError($errors, 'rule.wordlist.required', 'Comparator requires wordlist_id.', ['comparator' => $comparator]);
        }

        if (filled($wordlistId)) {
            if (! $rule->wordlist) {
                $this->pushError($errors, 'rule.wordlist.missing', 'Rule wordlist does not exist.', ['wordlist_id' => $wordlistId]);
            } else {
                $wordlistValidation = $this->validateWordlist($rule->wordlist, 'rule');
                $result['wordlist'] = $wordlistValidation;

                if ($wordlistValidation['status'] === 'failed') {
                    $this->pushError(
                        $errors,
                        'rule.wordlist.invalid',
                        'Rule wordlist failed validations.',
                        ['wordlist_id' => $rule->wordlist->id]
                    );
                }
            }
        }

        foreach ($rule->actions as $action) {
            $actionValidation = $this->validateAction($action);
            $result['actions'][] = $actionValidation;

            if ($actionValidation['status'] === 'failed') {
                $this->pushError(
                    $errors,
                    'rule.action.invalid',
                    'Rule action failed validations.',
                    ['action_id' => $action->id]
                );
            }
        }

        $result['errors'] = $errors;
        $result['status'] = empty($errors) ? 'passed' : 'failed';

        return $result;
    }

    protected function validateTarget(Target $target): array
    {
        $errors = [];

        $phase = $this->normalizePhase($target->getRawOriginal('phase'));
        $type = $this->normalizeTargetType($target->getRawOriginal('type'));
        $datatype = $this->normalizeDatatype($target->getRawOriginal('datatype'));
        $patternId = $target->getRawOriginal('pattern_id');
        $wordlistId = $target->getRawOriginal('wordlist_id');

        $result = [
            'target' => [
                'id' => $target->id,
                'name' => $target->name,
                'phase' => $phase,
                'type' => $type,
                'datatype' => $datatype,
                'pattern_id' => $patternId,
                'wordlist_id' => $wordlistId,
            ],
            'errors' => [],
            'wordlist' => null,
            'engines' => [
                'valid' => [],
                'invalid' => [],
            ],
            'final_datatype' => null,
            'status' => 'passed',
        ];

        if ($phase === null) {
            $this->pushError($errors, 'target.phase.invalid', 'Target phase is invalid.');
        }

        if ($type === null) {
            $this->pushError($errors, 'target.type.invalid', 'Target type is invalid.');
        }

        if ($datatype === null) {
            $this->pushError($errors, 'target.datatype.invalid', 'Target datatype is invalid.');
        }

        $hasPattern = filled($patternId);
        $pattern = $target->pattern;

        if ($hasPattern && (! $pattern)) {
            $this->pushError($errors, 'target.pattern.missing', 'Target pattern does not exist.', ['pattern_id' => $patternId]);
        }

        if (($type === TargetType::Getter->value) && $hasPattern) {
            $this->pushError($errors, 'target.pattern.not_allowed', 'Getter target cannot use pattern.');
        }

        if (($type !== null) && in_array($type, [TargetType::Full->value, TargetType::Meta->value], true) && (! $hasPattern)) {
            $this->pushError($errors, 'target.pattern.required', 'Target type full/meta requires pattern.');
        }

        if ($pattern) {
            $patternPhase = $this->normalizePhase($pattern->getRawOriginal('phase'));
            $patternType = $this->normalizeTargetType($pattern->getRawOriginal('type'));
            $patternDatatype = $this->normalizeDatatype($pattern->getRawOriginal('datatype'));

            if (($phase !== null) && ($patternPhase !== null) && ($phase !== $patternPhase)) {
                $this->pushError(
                    $errors,
                    'target.pattern.phase_mismatch',
                    'Pattern phase does not match target phase.',
                    ['target_phase' => $phase, 'pattern_phase' => $patternPhase, 'pattern_id' => $pattern->id]
                );
            }

            if (($type !== null) && ($patternType !== null) && ($type !== $patternType)) {
                $this->pushError(
                    $errors,
                    'target.pattern.type_mismatch',
                    'Pattern type does not match target type.',
                    ['target_type' => $type, 'pattern_type' => $patternType, 'pattern_id' => $pattern->id]
                );
            }

            if (($datatype !== null) && ($patternDatatype !== null) && ($datatype !== $patternDatatype)) {
                $this->pushError(
                    $errors,
                    'target.pattern.datatype_mismatch',
                    'Pattern datatype does not match target datatype.',
                    ['target_datatype' => $datatype, 'pattern_datatype' => $patternDatatype, 'pattern_id' => $pattern->id]
                );
            }
        }

        $requiresWordlist = ($datatype === DatatypeEnum::Array->value) && (! $hasPattern);

        if ($requiresWordlist && (! filled($wordlistId))) {
            $this->pushError(
                $errors,
                'target.wordlist.required',
                'Array target without pattern requires wordlist_id.'
            );
        }

        if (filled($wordlistId)) {
            if (! $target->wordlist) {
                $this->pushError($errors, 'target.wordlist.missing', 'Target wordlist does not exist.', ['wordlist_id' => $wordlistId]);
            } else {
                $wordlistValidation = $this->validateWordlist($target->wordlist, 'target');
                $result['wordlist'] = $wordlistValidation;

                if ($wordlistValidation['status'] === 'failed') {
                    $this->pushError(
                        $errors,
                        'target.wordlist.invalid',
                        'Target wordlist failed validations.',
                        ['wordlist_id' => $target->wordlist->id]
                    );
                }
            }
        }

        if ($target->engines->isNotEmpty()) {
            try {
                $traceBack = DatatypeService::traceBack([$target]);
                $detail = $traceBack['details'][$target->id] ?? array_values($traceBack['details'])[0] ?? null;

                if (! is_array($detail)) {
                    $this->pushError($errors, 'target.engines.traceback.missing', 'Unable to read traceback details for target engines.');
                } else {
                    $validEngines = array_values($detail['engines']['valid'] ?? []);
                    $invalidEngines = array_values($detail['engines']['invalid'] ?? []);

                    $result['engines'] = [
                        'valid' => $validEngines,
                        'invalid' => $invalidEngines,
                    ];

                    if (! empty($invalidEngines)) {
                        $this->pushError(
                            $errors,
                            'target.engines.invalid_chain',
                            'Target engines datatype chain is invalid.',
                            ['invalid_engines' => $invalidEngines]
                        );
                    }
                }
            } catch (Throwable $exception) {
                $this->pushError(
                    $errors,
                    'target.engines.traceback.exception',
                    'Unable to validate target engines chain.',
                    ['exception' => $exception::class, 'message' => $exception->getMessage()]
                );
            }
        }

        try {
            $result['final_datatype'] = $this->normalizeDatatype(DatatypeService::getFinal($target));
        } catch (Throwable $exception) {
            $this->pushError(
                $errors,
                'target.final_datatype.exception',
                'Unable to resolve target final datatype.',
                ['exception' => $exception::class, 'message' => $exception->getMessage()]
            );
        }

        $result['errors'] = $errors;
        $result['status'] = empty($errors) ? 'passed' : 'failed';

        return $result;
    }

    protected function validateWordlist(Wordlist $wordlist, string $context): array
    {
        $errors = [];

        $type = $this->normalizeWordlistType($wordlist->getRawOriginal('type'));
        $wordCountRaw = $wordlist->getRawOriginal('word_count');
        $wordCount = is_numeric($wordCountRaw) ? (int) $wordCountRaw : null;
        $countedWords = null;

        if ($type === null) {
            $this->pushError($errors, "$context.wordlist.type.invalid", 'Wordlist type is invalid.', ['wordlist_id' => $wordlist->id]);
        }

        if (($wordCount === null) || ($wordCount < 0)) {
            $this->pushError($errors, "$context.wordlist.word_count.invalid", 'Wordlist word_count is invalid.', ['wordlist_id' => $wordlist->id]);
        }

        if ($type === WordlistType::File->value) {
            $wordFile = $wordlist->getRawOriginal('word_file');

            if (! is_string($wordFile) || ($wordFile === '')) {
                $this->pushError($errors, "$context.wordlist.file.required", 'File wordlist requires word_file path.', ['wordlist_id' => $wordlist->id]);
            } elseif (! Storage::exists($wordFile)) {
                $this->pushError(
                    $errors,
                    "$context.wordlist.file.not_found",
                    'Wordlist file does not exist.',
                    ['wordlist_id' => $wordlist->id, 'word_file' => $wordFile]
                );
            } else {
                $content = Storage::get($wordFile);
                $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
                $countedWords = count(array_filter($lines, fn ($line) => filled(trim($line))));

                if (($wordCount !== null) && ($countedWords !== $wordCount)) {
                    $this->pushError(
                        $errors,
                        "$context.wordlist.word_count.mismatch",
                        'Wordlist word_count does not match file content lines.',
                        [
                            'wordlist_id' => $wordlist->id,
                            'word_count' => $wordCount,
                            'counted_words' => $countedWords,
                        ]
                    );
                }
            }
        }

        if ($type === WordlistType::Json->value) {
            $wordJson = null;

            try {
                $wordJson = $wordlist->word_json;
            } catch (Throwable $exception) {
                $this->pushError(
                    $errors,
                    "$context.wordlist.json.invalid",
                    'Wordlist word_json cannot be parsed.',
                    ['wordlist_id' => $wordlist->id, 'exception' => $exception::class]
                );
            }

            if (! is_array($wordJson)) {
                $this->pushError(
                    $errors,
                    "$context.wordlist.json.required",
                    'JSON wordlist requires word_json as array.',
                    ['wordlist_id' => $wordlist->id]
                );
            } else {
                if (! array_is_list($wordJson)) {
                    $this->pushError(
                        $errors,
                        "$context.wordlist.json.list_required",
                        'word_json must be a list of objects.',
                        ['wordlist_id' => $wordlist->id]
                    );
                }

                foreach ($wordJson as $index => $item) {
                    if (! is_array($item)) {
                        $this->pushError(
                            $errors,
                            "$context.wordlist.json.item_invalid",
                            'word_json item must be object with key "word".',
                            ['wordlist_id' => $wordlist->id, 'index' => $index]
                        );
                        continue;
                    }

                    if (! array_key_exists('word', $item) || (! is_string($item['word']))) {
                        $this->pushError(
                            $errors,
                            "$context.wordlist.json.word_invalid",
                            'word_json item must contain string field "word".',
                            ['wordlist_id' => $wordlist->id, 'index' => $index]
                        );
                    }
                }

                $countedWords = count($wordJson);
                if (($wordCount !== null) && ($countedWords !== $wordCount)) {
                    $this->pushError(
                        $errors,
                        "$context.wordlist.word_count.mismatch",
                        'Wordlist word_count does not match JSON word count.',
                        [
                            'wordlist_id' => $wordlist->id,
                            'word_count' => $wordCount,
                            'counted_words' => $countedWords,
                        ]
                    );
                }
            }
        }

        return [
            'wordlist' => [
                'id' => $wordlist->id,
                'name' => $wordlist->name,
                'type' => $type,
                'word_count' => $wordCount,
            ],
            'errors' => $errors,
            'meta' => [
                'counted_words' => $countedWords,
            ],
            'status' => empty($errors) ? 'passed' : 'failed',
        ];
    }

    protected function validateAction(Action $action): array
    {
        $errors = [];
        $type = $this->normalizeActionType($action->getRawOriginal('type'));
        $configurations = $action->configurations;

        $result = [
            'action' => [
                'id' => $action->id,
                'name' => $action->name,
                'type' => $type,
            ],
            'errors' => [],
            'status' => 'passed',
        ];

        if ($type === null) {
            $this->pushError($errors, 'action.type.invalid', 'Action type is invalid.', ['action_id' => $action->id]);
        } else {
            foreach ($this->validateActionConfigurations($type, $configurations) as $actionError) {
                $errors[] = $actionError;
            }
        }

        $result['errors'] = $errors;
        $result['status'] = empty($errors) ? 'passed' : 'failed';

        return $result;
    }

    protected function validateRuleConfigurations(string $comparator, mixed $configurations): array
    {
        $errors = [];
        $schema = self::RULE_CONFIGURATION_SCHEMA[$comparator] ?? null;

        if ($schema === null) {
            if (! $this->isConfigurationEmpty($configurations)) {
                $this->pushError($errors, 'rule.configurations.not_expected', 'Comparator does not require configurations.');
            }

            return [
                'errors' => $errors,
                'status' => empty($errors) ? 'passed' : 'failed',
            ];
        }

        $this->validateConfigurationAgainstSchema($configurations, $schema, 'rule.configurations', '$', $errors);

        if (($comparator === Comparator::InRange->value) && is_array($configurations)) {
            $from = $configurations['number_from'] ?? null;
            $to = $configurations['number_to'] ?? null;

            if (is_numeric($from) && is_numeric($to) && ((float) $from >= (float) $to)) {
                $this->pushError($errors, 'rule.configurations.range.invalid', 'number_from must be less than number_to.');
            }
        }

        return [
            'errors' => $errors,
            'status' => empty($errors) ? 'passed' : 'failed',
        ];
    }

    protected function validateActionConfigurations(string $type, mixed $configurations): array
    {
        $errors = [];
        $schema = self::ACTION_CONFIGURATION_SCHEMA[$type] ?? null;

        if ($schema === null) {
            if (! $this->isConfigurationEmpty($configurations)) {
                $this->pushError($errors, 'action.configurations.not_expected', "Action type '$type' does not require configurations.");
            }

            return $errors;
        }

        $this->validateConfigurationAgainstSchema($configurations, $schema, 'action.configurations', '$', $errors);

        if (($type === ActionType::Setter->value) && is_array($configurations)) {
            $this->validateSetterConfiguration($configurations, $errors);
        }

        return $errors;
    }

    protected function validateConfigurationAgainstSchema(
        mixed $value,
        array $schema,
        string $codePrefix,
        string $path,
        array &$errors
    ): void {
        $type = $schema['type'] ?? 'any';

        if (($value === null) && ! ($schema['nullable'] ?? false)) {
            $this->pushError(
                $errors,
                "$codePrefix.required",
                'Configuration field is required.',
                ['path' => $path]
            );

            return;
        }

        if (($value === null) && ($schema['nullable'] ?? false)) {
            return;
        }

        switch ($type) {
            case 'shape':
                if (! is_array($value)) {
                    $this->pushError(
                        $errors,
                        "$codePrefix.invalid_type",
                        'Configuration field has invalid type.',
                        ['path' => $path, 'expected' => 'object', 'actual' => get_debug_type($value)]
                    );

                    return;
                }

                $fields = $schema['fields'] ?? [];

                foreach ($fields as $field => $fieldSchema) {
                    $fieldPath = "$path.$field";
                    $exists = array_key_exists($field, $value);
                    $required = $fieldSchema['required'] ?? true;

                    if (! $exists) {
                        if ($required) {
                            $this->pushError(
                                $errors,
                                "$codePrefix.required",
                                'Configuration field is required.',
                                ['path' => $fieldPath]
                            );
                        }

                        continue;
                    }

                    $this->validateConfigurationAgainstSchema($value[$field], $fieldSchema, $codePrefix, $fieldPath, $errors);
                }

                if (($schema['allow_extra'] ?? true) === false) {
                    foreach (array_keys($value) as $key) {
                        if (! array_key_exists($key, $fields)) {
                            $this->pushError(
                                $errors,
                                "$codePrefix.unexpected_field",
                                'Configuration contains unsupported field.',
                                ['path' => "$path.$key"]
                            );
                        }
                    }
                }
                break;

            case 'list':
                if (! is_array($value) || (! array_is_list($value))) {
                    $this->pushError(
                        $errors,
                        "$codePrefix.invalid_type",
                        'Configuration field has invalid type.',
                        ['path' => $path, 'expected' => 'list', 'actual' => get_debug_type($value)]
                    );

                    return;
                }

                $minItems = $schema['min_items'] ?? null;
                if (is_int($minItems) && (count($value) < $minItems)) {
                    $this->pushError(
                        $errors,
                        "$codePrefix.min_items",
                        'Configuration list has fewer items than required.',
                        ['path' => $path, 'min_items' => $minItems]
                    );
                }

                $itemSchema = $schema['item'] ?? null;
                if (is_array($itemSchema)) {
                    foreach ($value as $index => $item) {
                        $this->validateConfigurationAgainstSchema($item, $itemSchema, $codePrefix, "$path[$index]", $errors);
                    }
                }
                break;

            case 'string':
                if (! is_string($value)) {
                    $this->pushError(
                        $errors,
                        "$codePrefix.invalid_type",
                        'Configuration field has invalid type.',
                        ['path' => $path, 'expected' => 'string', 'actual' => get_debug_type($value)]
                    );
                }
                break;

            case 'bool':
                if (! is_bool($value)) {
                    $this->pushError(
                        $errors,
                        "$codePrefix.invalid_type",
                        'Configuration field has invalid type.',
                        ['path' => $path, 'expected' => 'boolean', 'actual' => get_debug_type($value)]
                    );
                }
                break;

            case 'numeric':
                if (! is_numeric($value)) {
                    $this->pushError(
                        $errors,
                        "$codePrefix.invalid_type",
                        'Configuration field has invalid type.',
                        ['path' => $path, 'expected' => 'numeric', 'actual' => get_debug_type($value)]
                    );
                } else {
                    $min = $schema['min'] ?? null;
                    if (($min !== null) && ((float) $value < $min)) {
                        $this->pushError(
                            $errors,
                            "$codePrefix.out_of_range",
                            'Configuration numeric field is out of allowed range.',
                            ['path' => $path, 'min' => $min]
                        );
                    }
                }
                break;

            case 'integer':
                if (! $this->isIntegerLike($value)) {
                    $this->pushError(
                        $errors,
                        "$codePrefix.invalid_type",
                        'Configuration field has invalid type.',
                        ['path' => $path, 'expected' => 'integer', 'actual' => get_debug_type($value)]
                    );
                } else {
                    $min = $schema['min'] ?? null;
                    if (($min !== null) && ((int) $value < $min)) {
                        $this->pushError(
                            $errors,
                            "$codePrefix.out_of_range",
                            'Configuration integer field is out of allowed range.',
                            ['path' => $path, 'min' => $min]
                        );
                    }
                }
                break;

            case 'enum':
                $allowedValues = $schema['values'] ?? [];
                if (! in_array($value, $allowedValues, true)) {
                    $this->pushError(
                        $errors,
                        "$codePrefix.invalid_value",
                        'Configuration field has invalid value.',
                        ['path' => $path, 'allowed' => $allowedValues]
                    );
                }
                break;

            case 'http_status':
                if (! is_numeric($value) || (! array_key_exists((int) $value, Response::$statusTexts))) {
                    $this->pushError(
                        $errors,
                        "$codePrefix.invalid_value",
                        'Configuration field has invalid value.',
                        ['path' => $path, 'expected' => 'valid_http_status']
                    );
                }
                break;

            case 'method':
                if (! $this->isValidMethod($value)) {
                    $this->pushError(
                        $errors,
                        "$codePrefix.invalid_value",
                        'Configuration field has invalid value.',
                        ['path' => $path, 'expected' => 'valid_http_method']
                    );
                }
                break;

            case 'any':
            default:
                break;
        }
    }

    protected function validateSetterConfiguration(array $configurations, array &$errors): void
    {
        $directive = $configurations['directive'] ?? null;
        $execution = $configurations['execution'] ?? null;

        if (! in_array($directive, ['set', 'unset'], true)) {
            return;
        }

        if (! is_array($execution) || (! array_is_list($execution))) {
            return;
        }

        foreach ($execution as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            if ($directive === 'set') {
                if (! array_key_exists('datatype', $item)) {
                    $this->pushError(
                        $errors,
                        'action.setter.execution.required',
                        'Setter set execution requires datatype.',
                        ['path' => '$.execution['.$index.'].datatype']
                    );
                }

                if (! array_key_exists('value', $item)) {
                    $this->pushError(
                        $errors,
                        'action.setter.execution.required',
                        'Setter set execution requires value.',
                        ['path' => '$.execution['.$index.'].value']
                    );
                    continue;
                }

                $datatype = $item['datatype'] ?? null;
                $value = $item['value'];

                if (($datatype === DatatypeEnum::Number->value) && (! is_numeric($value))) {
                    $this->pushError(
                        $errors,
                        'action.setter.execution.value.invalid',
                        'Setter set execution value must be numeric when datatype is number.',
                        ['path' => '$.execution['.$index.'].value']
                    );
                }

                if (($datatype === DatatypeEnum::String->value) && (! is_string($value))) {
                    $this->pushError(
                        $errors,
                        'action.setter.execution.value.invalid',
                        'Setter set execution value must be string when datatype is string.',
                        ['path' => '$.execution['.$index.'].value']
                    );
                }
            }

            if ($directive === 'unset') {
                if (array_key_exists('datatype', $item)) {
                    $this->pushError(
                        $errors,
                        'action.setter.execution.unexpected_field',
                        'Setter unset execution does not support datatype field.',
                        ['path' => '$.execution['.$index.'].datatype']
                    );
                }

                if (array_key_exists('value', $item)) {
                    $this->pushError(
                        $errors,
                        'action.setter.execution.unexpected_field',
                        'Setter unset execution does not support value field.',
                        ['path' => '$.execution['.$index.'].value']
                    );
                }
            }
        }
    }

    protected function isConfigurationEmpty(mixed $configurations): bool
    {
        return ($configurations === null) || ($configurations === []);
    }

    protected function isIntegerLike(mixed $value): bool
    {
        if (is_int($value)) {
            return true;
        }

        if (is_float($value)) {
            return floor($value) === $value;
        }

        return is_string($value) && preg_match('/^-?\d+$/', $value) === 1;
    }

    protected function pushError(array &$errors, string $code, string $message, array $context = []): void
    {
        $errors[] = [
            'code' => $code,
            'message' => $message,
            'context' => $context,
        ];
    }

    protected function normalizePhase(mixed $value): ?int
    {
        $phase = $value instanceof Phase ? $value->value : (is_numeric($value) ? (int) $value : null);

        return $phase !== null ? Phase::tryFrom($phase)?->value : null;
    }

    protected function normalizeDatatype(mixed $value): ?string
    {
        $datatype = $value instanceof DatatypeEnum ? $value->value : (is_string($value) ? $value : null);

        return $datatype !== null ? DatatypeEnum::tryFrom($datatype)?->value : null;
    }

    protected function normalizeComparator(mixed $value): ?string
    {
        $comparator = $value instanceof Comparator ? $value->value : (is_string($value) ? $value : null);

        return $comparator !== null ? Comparator::tryFrom($comparator)?->value : null;
    }

    protected function normalizeTargetType(mixed $value): ?string
    {
        $type = $value instanceof TargetType ? $value->value : (is_string($value) ? $value : null);

        return $type !== null ? TargetType::tryFrom($type)?->value : null;
    }

    protected function normalizeWordlistType(mixed $value): ?string
    {
        $type = $value instanceof WordlistType ? $value->value : (is_string($value) ? $value : null);

        return $type !== null ? WordlistType::tryFrom($type)?->value : null;
    }

    protected function normalizeActionType(mixed $value): ?string
    {
        $type = $value instanceof ActionType ? $value->value : (is_string($value) ? $value : null);

        return $type !== null ? ActionType::tryFrom($type)?->value : null;
    }

    protected function isValidMethod(mixed $value): bool
    {
        if ($value instanceof Method) {
            return true;
        }

        return is_string($value) && (Method::tryFrom($value) !== null);
    }
}
