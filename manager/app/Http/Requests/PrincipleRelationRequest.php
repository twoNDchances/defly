<?php

namespace App\Http\Requests;

use App\Models\Principle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class PrincipleRelationRequest extends RelationRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        if (! $this->isMethod('post') || $this->relationTable() !== 'rules') {
            return $rules;
        }

        $principle = $this->route('principle');

        if (! $principle instanceof Principle) {
            return $rules;
        }

        $rules['ids.*'] = [
            'required',
            'string',
            'distinct',
            Rule::exists('rules', 'id')->where('phase', $principle->getRawOriginal('phase')),
        ];

        return $rules;
    }

    protected function routeKey(): string
    {
        return 'principle';
    }
}
