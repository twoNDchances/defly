<?php

namespace App\Http\Requests;

use App\Models\Target;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use App\Traits\Validators\TargetValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TargetRequest extends FormRequest
{
    use Authorization, Error, TargetValidator;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->authorizeResourceRequest(Target::class, 'target');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $target = $this->route('target');
        $ignore = $target instanceof Target ? $target->getKey() : null;

        return match (true) {
            $this->isMethod('get') => $this->paginationRules(),
            $this->isMethod('post'),
            $this->isMethod('put'),
            $this->isMethod('patch') => self::validateTarget($ignore),
            default => [],
        };
    }

    public function validationData(): array
    {
        $data = parent::validationData();
        $target = $this->route('target');

        if (! $this->isMethod('patch') || ! $target instanceof Target) {
            return $data;
        }

        return array_replace($this->modelData($target, [
            'phase',
            'type',
            'pattern_id',
            'name',
            'datatype',
            'wordlist_id',
            'description',
        ]), $data);
    }
}
