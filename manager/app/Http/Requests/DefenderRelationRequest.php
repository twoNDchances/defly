<?php

namespace App\Http\Requests;

use App\Enums\Principle\ValidationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class DefenderRelationRequest extends RelationRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        if (! $this->isMethod('post') || $this->relationTable() !== 'principles') {
            return $rules;
        }

        $rules['ids.*'] = [
            'required',
            'string',
            'distinct',
            Rule::exists('principles', 'id')->where('validation_status', ValidationStatus::Passed->value),
        ];

        return $rules;
    }

    protected function routeKey(): string
    {
        return 'defender';
    }
}
