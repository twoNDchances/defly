<?php

namespace App\Http\Requests;

use App\Models\Group;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use App\Traits\Validators\GroupValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GroupRequest extends FormRequest
{
    use Authorization, Error, GroupValidator;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->authorizeResourceRequest(Group::class, 'group');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $group = $this->route('group');
        $ignore = $group instanceof Group ? $group->getKey() : null;

        return match (true) {
            $this->isMethod('get') => $this->paginationRules(),
            $this->isMethod('post'),
            $this->isMethod('put'),
            $this->isMethod('patch') => self::validateGroup($ignore),
            default => [],
        };
    }

    public function validationData(): array
    {
        $data = parent::validationData();
        $group = $this->route('group');

        if (! $this->isMethod('patch') || ! $group instanceof Group) {
            return $data;
        }

        return array_replace($this->modelData($group, [
            'name',
            'description',
        ]), $data);
    }
}
