<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Services\Security;
use App\Traits\Requests\Error;
use App\Traits\Validators\UserValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    use Error, UserValidator;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');

        return match (true) {
            $this->isMethod('get') => $user instanceof User
                ? $this->canAccessUser($user, 'view')
                : Security::can(User::class, 'viewAny', $this->user()),
            $this->isMethod('post') => Security::can(User::class, 'create', $this->user()),
            $this->isMethod('put'),
            $this->isMethod('patch') => $user instanceof User && $this->canAccessUser($user, 'update'),
            $this->isMethod('delete') => $user instanceof User && $this->canAccessUser($user, 'delete'),
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
        $user = $this->route('user');
        $ignore = $user instanceof User ? $user->getKey() : null;

        return match (true) {
            $this->isMethod('get') => [
                'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            ],
            $this->isMethod('post') => [
                ...self::validateUser($ignore),
                'is_root' => self::validateIsRoot($this->user()?->is_root ? 'required' : 'nullable'),
            ],
            $this->isMethod('put') => [
                'name' => self::validateName(),
                'email' => self::validateEmail(ignore: $ignore),
                'password' => self::validatePassword('nullable'),
                'is_activated' => self::validateIsActivated(),
                'is_root' => self::validateIsRoot($this->user()?->is_root ? 'required' : 'nullable'),
            ],
            $this->isMethod('patch') => [
                'name' => self::validateName('sometimes'),
                'email' => self::validateEmail('sometimes', $ignore),
                'password' => self::validatePassword('nullable'),
                'is_activated' => self::validateIsActivated('sometimes'),
                'is_root' => self::validateIsRoot('sometimes'),
            ],
            default => [],
        };
    }

    private function canAccessUser(User $user, string $action): bool
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

        return Security::can(User::class, $action, $currentUser);
    }
}
