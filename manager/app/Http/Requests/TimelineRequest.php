<?php

namespace App\Http\Requests;

use App\Models\Timeline;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TimelineRequest extends FormRequest
{
    use Authorization, Error;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->authorizeResourceRequest(Timeline::class, 'timeline');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return match (true) {
            $this->isMethod('get') => $this->paginationRules(),
            default => [],
        };
    }
}
