<?php

namespace App\Http\Requests;

use App\Models\Engine;
use App\Traits\Filament\Specifics\Engine\EngineData;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use App\Traits\Validators\EngineValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EngineRequest extends FormRequest
{
    use Authorization, EngineData, EngineValidator, Error;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->authorizeResourceRequest(Engine::class, 'engine');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $engine = $this->route('engine');
        $ignore = $engine instanceof Engine ? $engine->getKey() : null;

        return match (true) {
            $this->isMethod('get') => $this->paginationRules(),
            $this->isMethod('post'),
            $this->isMethod('put'),
            $this->isMethod('patch') => self::validateEngine($ignore),
            default => [],
        };
    }

    public function validationData(): array
    {
        $data = parent::validationData();
        $engine = $this->route('engine');

        if (! $this->isMethod('patch') || ! $engine instanceof Engine) {
            return $data;
        }

        return array_replace($this->loadedEngineData($engine), $data);
    }

    private function loadedEngineData(Engine $engine): array
    {
        return self::loadForm($this->modelData($engine, [
            'name',
            'input_datatype',
            'type',
            'configurations',
            'output_datatype',
            'description',
        ]));
    }
}
