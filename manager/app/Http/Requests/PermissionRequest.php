<?php

namespace App\Http\Requests;

use App\Models\Permission;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use App\Traits\Validators\PermissionValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends FormRequest
{
    use Authorization, Error, PermissionValidator;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->authorizeResourceRequest(Permission::class, 'permission');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $permission = $this->route('permission');
        $ignore = $permission instanceof Permission ? $permission->getKey() : null;

        return match (true) {
            $this->isMethod('get') => $this->paginationRules(),
            $this->isMethod('post'),
            $this->isMethod('put'),
            $this->isMethod('patch') => self::validatePermission($ignore),
            default => [],
        };
    }

    public function validationData(): array
    {
        $data = parent::validationData();
        $permission = $this->route('permission');

        if (! $this->isMethod('patch') || ! $permission instanceof Permission) {
            return $data;
        }

        return array_replace($this->modelData($permission, [
            'name',
            'description',
            'applied_for',
            'action',
        ]), $data);
    }
}
