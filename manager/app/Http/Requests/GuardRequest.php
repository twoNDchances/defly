<?php

namespace App\Http\Requests;

use App\Models\Guard;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use App\Traits\Validators\GuardValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GuardRequest extends FormRequest
{
    use Authorization, Error, GuardValidator;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->authorizeResourceRequest(Guard::class, 'guard');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $guard = $this->route('guard');
        $ignore = $guard instanceof Guard ? $guard->getKey() : null;

        return match (true) {
            $this->isMethod('get') => $this->paginationRules(),
            $this->isMethod('post'),
            $this->isMethod('put'),
            $this->isMethod('patch') => self::validateGuard($ignore),
            default => [],
        };
    }

    public function validationData(): array
    {
        $data = parent::validationData();
        $guard = $this->route('guard');

        if (! $this->isMethod('patch') || ! $guard instanceof Guard) {
            return $data;
        }

        return array_replace($this->modelData($guard, [
            'name',
            'description',
            'expired_at',
        ]), $data);
    }
}
