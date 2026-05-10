<?php

namespace App\Http\Requests;

use App\Models\Defender;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DefenderActionRequest extends FormRequest
{
    use Authorization, Error;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $defender = $this->route('defender');
        $ability = $this->ability();

        if (! $this->isMethod('post') || ! $defender instanceof Defender || ! in_array($ability, ['deploy', 'cancel', 'follow'], true)) {
            return false;
        }

        if (! $this->allows($ability, $defender)) {
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
