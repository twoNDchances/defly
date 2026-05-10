<?php

namespace App\Http\Requests;

use App\Models\Defender;
use App\Traits\Filament\Specifics\Defender\DefenderData;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use App\Traits\Validators\DefenderValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DefenderRequest extends FormRequest
{
    use Authorization, DefenderData, DefenderValidator, Error;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->authorizeResourceRequest(Defender::class, 'defender');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $defender = $this->route('defender');
        $ignore = $defender instanceof Defender ? $defender->getKey() : null;

        return match (true) {
            $this->isMethod('get') => $this->paginationRules(),
            $this->isMethod('post'),
            $this->isMethod('put'),
            $this->isMethod('patch') => self::validateDefender($ignore),
            default => [],
        };
    }

    public function validationData(): array
    {
        $data = parent::validationData();
        $defender = $this->route('defender');

        if (! $this->isMethod('patch') || ! $defender instanceof Defender) {
            return $data;
        }

        return array_replace(self::loadForm($this->modelData($defender, [
            'name',
            'proxy_port',
            'environment_variables',
            'description',
        ])), $data);
    }
}
