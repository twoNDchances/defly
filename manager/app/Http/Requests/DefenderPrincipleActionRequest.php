<?php

namespace App\Http\Requests;

use App\Models\Defender;
use App\Models\Principle;
use App\Services\Security;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DefenderPrincipleActionRequest extends FormRequest
{
    use Authorization, Error;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $defender = $this->route('defender');
        $principle = $this->route('principle');
        $ability = $this->ability();

        if (! $this->isMethod('post')
            || ! $defender instanceof Defender
            || ! $principle instanceof Principle
            || ! in_array($ability, ['apply', 'revoke'], true)
            || ! Security::canOperateDefender($defender, $this->user())
            || ! $this->allows($ability, $principle)) {
            return false;
        }

        return true;
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
