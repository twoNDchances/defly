<?php

namespace App\Http\Requests;

use App\Models\Decision;
use App\Models\Defender;
use App\Services\Security;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DefenderDecisionActionRequest extends FormRequest
{
    use Authorization, Error;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $defender = $this->route('defender');
        $decision = $this->route('decision');
        $ability = $this->ability();

        if (! $this->isMethod('post')
            || ! $defender instanceof Defender
            || ! $decision instanceof Decision
            || ! in_array($ability, ['implement', 'suspend'], true)
            || ! Security::canOperateDefender($defender, $this->user())
            || ! $this->allows($ability, $decision)) {
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
