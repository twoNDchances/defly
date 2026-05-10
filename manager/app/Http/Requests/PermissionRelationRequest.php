<?php

namespace App\Http\Requests;

use App\Models\Permission;
use App\Models\User;
use App\Services\Security;
use App\Traits\Requests\Error;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRelationRequest extends FormRequest
{
    use Error;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $permission = $this->route('permission');

        return match (true) {
            $this->isMethod('get'),
            $this->isMethod('post'),
            $this->isMethod('delete') => $permission instanceof Permission && $this->canUpdatePermission(),
            default => false,
        };
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return match (true) {
            $this->isMethod('get') => [],
            $this->isMethod('post'),
            $this->isMethod('delete') => [
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['required', 'string', 'distinct', Rule::exists($this->relationTable(), 'id')],
            ],
            default => [],
        };
    }

    private function canUpdatePermission(): bool
    {
        $currentUser = $this->user();

        if (! $currentUser instanceof User) {
            return false;
        }

        return Security::can(Permission::class, 'update', $currentUser);
    }

    private function relationTable(): string
    {
        $routeName = (string) $this->route()?->getName();
        $segments = explode('.', $routeName);

        return $segments[3] ?? '';
    }
}
