<?php

namespace App\Http\Requests;

use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class RelationRequest extends FormRequest
{
    use Authorization, Error;

    abstract protected function routeKey(): string;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $record = $this->route($this->routeKey());

        return match (true) {
            $this->isMethod('get'),
            $this->isMethod('post'),
            $this->isMethod('delete') => $record instanceof Model && $this->allows('update', $record),
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

    protected function relationTable(): string
    {
        $routeName = (string) $this->route()?->getName();
        $segments = explode('.', $routeName);

        return $segments[3] ?? '';
    }
}
