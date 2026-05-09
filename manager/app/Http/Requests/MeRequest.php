<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Traits\Requests\Error;
use App\Traits\Validators\UserValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MeRequest extends FormRequest
{
    use Error, UserValidator;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() instanceof User;
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
            $this->isMethod('put') => [
                'name' => self::validateName(),
                'email' => self::validateEmail(ignore: $this->user()?->getKey()),
                ...$this->sensitiveFieldRules(),
            ],
            $this->isMethod('patch') => [
                'name' => self::validateName('sometimes'),
                'email' => self::validateEmail('sometimes', $this->user()?->getKey()),
                ...$this->sensitiveFieldRules(),
            ],
            default => [],
        };
    }

    private function emailChanged(): bool
    {
        $email = $this->input('email');

        return is_string($email)
            && $this->user() instanceof User
            && $email !== $this->user()->email;
    }

    private function passwordChanged(): bool
    {
        return $this->filled('password');
    }

    private function sensitiveFieldRules(): array
    {
        return [
            'current_password' => [
                $this->emailChanged() || $this->passwordChanged() ? 'required' : 'sometimes',
                'string',
                'current_password',
            ],
            'password' => [
                ...self::validatePassword('sometimes'),
                'confirmed',
            ],
        ];
    }
}
