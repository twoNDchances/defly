<?php

namespace App\Http\Requests;

use App\Models\Rule;
use App\Traits\Filament\Specifics\Rule\RuleData;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use App\Traits\Validators\RuleValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RuleRequest extends FormRequest
{
    use Authorization, Error, RuleData, RuleValidator;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->authorizeResourceRequest(Rule::class, 'rule');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rule = $this->route('rule');
        $ignore = $rule instanceof Rule ? $rule->getKey() : null;

        return match (true) {
            $this->isMethod('get') => $this->paginationRules(),
            $this->isMethod('post'),
            $this->isMethod('put'),
            $this->isMethod('patch') => self::validateRule($ignore),
            default => [],
        };
    }

    public function validationData(): array
    {
        $data = parent::validationData();
        $rule = $this->route('rule');

        if (! $this->isMethod('patch') || ! $rule instanceof Rule) {
            return $data;
        }

        return array_replace($this->loadedRuleData($rule), $data);
    }

    private function loadedRuleData(Rule $rule): array
    {
        return self::loadForm($this->modelData($rule, [
            'name',
            'phase',
            'target_id',
            'comparator',
            'is_inversed',
            'configurations',
            'wordlist_id',
            'description',
        ]));
    }
}
