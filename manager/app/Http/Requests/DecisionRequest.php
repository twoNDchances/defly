<?php

namespace App\Http\Requests;

use App\Models\Decision;
use App\Traits\Filament\Specifics\Decision\DecisionData;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use App\Traits\Validators\DecisionValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DecisionRequest extends FormRequest
{
    use Authorization, DecisionData, DecisionValidator, Error;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->authorizeResourceRequest(Decision::class, 'decision');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $decision = $this->route('decision');
        $ignore = $decision instanceof Decision ? $decision->getKey() : null;

        return match (true) {
            $this->isMethod('get') => $this->paginationRules(),
            $this->isMethod('post'),
            $this->isMethod('put'),
            $this->isMethod('patch') => self::validateDecision($ignore),
            default => [],
        };
    }

    public function validationData(): array
    {
        $data = parent::validationData();
        $decision = $this->route('decision');

        if (! $this->isMethod('patch') || ! $decision instanceof Decision) {
            return $data;
        }

        return array_replace($this->loadedDecisionData($decision), $data);
    }

    private function loadedDecisionData(Decision $decision): array
    {
        return self::loadForm($this->modelData($decision, [
            'name',
            'direction',
            'condition',
            'score',
            'action',
            'configurations',
            'description',
        ]));
    }
}
