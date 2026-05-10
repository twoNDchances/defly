<?php

namespace App\Http\Requests;

use App\Models\Action;
use App\Traits\Filament\Specifics\Action\ActionData;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use App\Traits\Validators\ActionValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ActionRequest extends FormRequest
{
    use ActionData, ActionValidator, Authorization, Error;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->authorizeResourceRequest(Action::class, 'action');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $action = $this->route('action');
        $ignore = $action instanceof Action ? $action->getKey() : null;

        return match (true) {
            $this->isMethod('get') => $this->paginationRules(),
            $this->isMethod('post'),
            $this->isMethod('put'),
            $this->isMethod('patch') => self::validateAction($ignore),
            default => [],
        };
    }

    public function validationData(): array
    {
        $data = parent::validationData();
        $action = $this->route('action');

        if (! $this->isMethod('patch') || ! $action instanceof Action) {
            return $data;
        }

        return array_replace($this->loadedActionData($action), $data);
    }

    private function loadedActionData(Action $action): array
    {
        return self::loadForm($this->modelData($action, [
            'name',
            'type',
            'configurations',
            'description',
        ]));
    }
}
