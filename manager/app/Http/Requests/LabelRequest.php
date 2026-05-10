<?php

namespace App\Http\Requests;

use App\Models\Label;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use App\Traits\Validators\LabelValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LabelRequest extends FormRequest
{
    use Authorization, Error, LabelValidator;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->authorizeResourceRequest(Label::class, 'label');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $label = $this->route('label');
        $ignore = $label instanceof Label ? $label->getKey() : null;

        return match (true) {
            $this->isMethod('get') => $this->paginationRules(),
            $this->isMethod('post'),
            $this->isMethod('put'),
            $this->isMethod('patch') => self::validateLabel($ignore),
            default => [],
        };
    }

    public function validationData(): array
    {
        $data = parent::validationData();
        $label = $this->route('label');

        if (! $this->isMethod('patch') || ! $label instanceof Label) {
            return $data;
        }

        return array_replace($this->modelData($label, [
            'name',
            'color',
            'description',
        ]), $data);
    }
}
