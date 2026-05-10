<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Services\Security;
use App\Traits\Requests\Error;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRelationRequest extends FormRequest
{
    use Error;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');

        return match (true) {
            $this->isMethod('get'),
            $this->isMethod('post'),
            $this->isMethod('delete') => $user instanceof User && $this->canAccessUserForUpdate($user),
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

    private function canAccessUserForUpdate(User $user): bool
    {
        $currentUser = $this->user();

        if (! $currentUser instanceof User) {
            return false;
        }

        if (! $currentUser->is_root && $user->is_root) {
            return false;
        }

        if ($currentUser->is($user)) {
            return false;
        }

        return Security::can(User::class, 'update', $currentUser);
    }

    private function relationTable(): string
    {
        $routeName = (string) $this->route()?->getName();
        $segments = explode('.', $routeName);

        return $segments[3] ?? '';
    }
}
