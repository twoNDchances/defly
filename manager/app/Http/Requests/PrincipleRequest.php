<?php

namespace App\Http\Requests;

use App\Models\Principle;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use App\Traits\Validators\PrincipleValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PrincipleRequest extends FormRequest
{
    use Authorization, Error, PrincipleValidator;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->authorizeResourceRequest(Principle::class, 'principle');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $principle = $this->route('principle');
        $ignore = $principle instanceof Principle ? $principle->getKey() : null;

        return match (true) {
            $this->isMethod('get') => $this->paginationRules(),
            $this->isMethod('post'),
            $this->isMethod('put'),
            $this->isMethod('patch') => self::validatePrinciple($ignore),
            default => [],
        };
    }

    public function validationData(): array
    {
        $data = parent::validationData();
        $principle = $this->route('principle');

        if (! $this->isMethod('patch') || ! $principle instanceof Principle) {
            return $data;
        }

        return array_replace($this->modelData($principle, [
            'name',
            'level',
            'phase',
            'validation_status',
            'description',
        ]), $data);
    }
}
