<?php

namespace App\Http\Controllers;

use App\Enums\Phase;
use App\Enums\Rule\Comparator;
use App\Http\Requests\RuleRequest;
use App\Models\Rule;
use App\Services\ApiPayload;
use App\Traits\Filament\Specifics\Rule\RuleData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class RuleController extends Controller
{
    use RuleData;

    public function index(RuleRequest $request): JsonResponse
    {
        $rules = Rule::query()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($rules);
    }

    public function store(RuleRequest $request): JsonResponse
    {
        $rule = Rule::query()->create($this->ruleData($request));

        return response()->json($rule, SymfonyResponse::HTTP_CREATED);
    }

    public function payload(): JsonResponse
    {
        return response()->json(ApiPayload::resource('rules', [
            'store_similar' => [
                'method' => 'POST',
                'body' => $this->payloadBody('array-similar', '<array-target-id>', Comparator::Similar->value, [
                    'wordlist_id' => '<wordlist-id>',
                ]),
            ],
            'store_contains' => [
                'method' => 'POST',
                'body' => $this->payloadBody('array-contains', '<array-target-id>', Comparator::Contains->value, [
                    'string_value' => 'blocked-token',
                ]),
            ],
            'store_match' => [
                'method' => 'POST',
                'body' => $this->payloadBody('array-match', '<array-target-id>', Comparator::Match->value, [
                    'string_value' => 'exact-value',
                ]),
            ],
            'store_search' => [
                'method' => 'POST',
                'body' => $this->payloadBody('array-search', '<array-target-id>', Comparator::Search->value, [
                    'wordlist_id' => '<wordlist-id>',
                ]),
            ],
            'store_equal' => [
                'method' => 'POST',
                'body' => $this->payloadBody('number-equal', '<number-target-id>', Comparator::Equal->value, [
                    'number_value' => 10,
                ]),
            ],
            'store_greater_than' => [
                'method' => 'POST',
                'body' => $this->payloadBody('number-greater-than', '<number-target-id>', Comparator::GreaterThan->value, [
                    'number_value' => 10,
                ]),
            ],
            'store_less_than' => [
                'method' => 'POST',
                'body' => $this->payloadBody('number-less-than', '<number-target-id>', Comparator::LessThan->value, [
                    'number_value' => 10,
                ]),
            ],
            'store_greater_than_or_equal' => [
                'method' => 'POST',
                'body' => $this->payloadBody('number-greater-than-or-equal', '<number-target-id>', Comparator::GreaterThanOrEqual->value, [
                    'number_value' => 10,
                ]),
            ],
            'store_less_than_or_equal' => [
                'method' => 'POST',
                'body' => $this->payloadBody('number-less-than-or-equal', '<number-target-id>', Comparator::LessThanOrEqual->value, [
                    'number_value' => 10,
                ]),
            ],
            'store_in_range' => [
                'method' => 'POST',
                'body' => $this->payloadBody('number-in-range', '<number-target-id>', Comparator::InRange->value, [
                    'number_from_value' => 1,
                    'number_to_value' => 10,
                ]),
            ],
            'store_mirror' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-mirror', '<string-target-id>', Comparator::Mirror->value, [
                    'string_value' => 'needle',
                ]),
            ],
            'store_starts_with' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-starts-with', '<string-target-id>', Comparator::StartsWith->value, [
                    'string_value' => 'Bearer ',
                ]),
            ],
            'store_ends_with' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-ends-with', '<string-target-id>', Comparator::EndsWith->value, [
                    'string_value' => '.php',
                ]),
            ],
            'store_check' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-check', '<string-target-id>', Comparator::Check->value, [
                    'wordlist_id' => '<wordlist-id>',
                ]),
            ],
            'store_reg_exp' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-reg-exp', '<string-target-id>', Comparator::RegExp->value, [
                    'string_value' => '/admin',
                ]),
            ],
            'store_check_reg_exp' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-check-reg-exp', '<string-target-id>', Comparator::CheckRegExp->value, [
                    'wordlist_id' => '<wordlist-id>',
                ]),
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '{rule}',
                'body' => [
                    'description' => 'Updated rule description.',
                ],
            ],
        ]));
    }

    public function show(RuleRequest $request, Rule $rule): JsonResponse
    {
        return response()->json($rule);
    }

    public function update(RuleRequest $request, Rule $rule): JsonResponse
    {
        $rule->update($this->ruleData($request));

        return response()->json($rule->refresh());
    }

    public function destroy(RuleRequest $request, Rule $rule): HttpResponse
    {
        $rule->delete();

        return response()->noContent();
    }

    private function ruleData(RuleRequest $request): array
    {
        $data = self::saveForm($request->validated());

        return $this->onlyFields($data, [
            'name',
            'phase',
            'target_id',
            'comparator',
            'is_inversed',
            'configurations',
            'wordlist_id',
            'description',
        ]);
    }

    private function payloadBody(string $name, string $targetId, string $comparator, array $extra = []): array
    {
        return [
            'name' => $name,
            'phase' => Phase::One->value,
            'target_id' => $targetId,
            'comparator' => $comparator,
            'is_inversed' => false,
            'description' => 'Rule API example.',
            ...$extra,
        ];
    }
}
