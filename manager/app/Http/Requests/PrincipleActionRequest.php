<?php

namespace App\Http\Requests;

use App\Models\Principle;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PrincipleActionRequest extends FormRequest
{
    use Authorization, Error;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $principle = $this->route('principle');

        return $this->isMethod('post')
            && $principle instanceof Principle
            && $this->ability() === 'validate'
            && $this->allows('validate', $principle);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }

    private function ability(): string
    {
        $segments = explode('.', (string) $this->route()?->getName());

        return (string) end($segments);
    }
}
