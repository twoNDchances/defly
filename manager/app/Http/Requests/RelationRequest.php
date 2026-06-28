<?php

namespace App\Http\Requests;

use App\Models\Action;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Engine;
use App\Models\Group;
use App\Models\Label;
use App\Models\Permission;
use App\Models\Principle;
use App\Models\Rule as RuleModel;
use App\Models\Target;
use App\Models\User;
use App\Models\Wordlist;
use App\Services\Security;
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
            $this->isMethod('get') => $record instanceof Model && $this->canViewOwnerRecord($record),
            $this->isMethod('post'),
            $this->isMethod('delete') => $record instanceof Model
                && $this->canUpdateOwnerRecord($record)
                && $this->canViewRelatedRecords(),
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

    protected function canViewOwnerRecord(Model $record): bool
    {
        return $this->allows('view', $record);
    }

    protected function canUpdateOwnerRecord(Model $record): bool
    {
        return $this->allows('update', $record);
    }

    protected function canViewRelatedRecords(): bool
    {
        $ids = $this->input('ids');

        if (! is_array($ids) || $ids === []) {
            return true;
        }

        $modelClass = $this->relationModelClass();

        if ($modelClass === null) {
            return false;
        }

        foreach ($ids as $id) {
            if (! is_scalar($id)) {
                continue;
            }

            $record = $modelClass::query()->find((string) $id);

            if (! $record instanceof Model) {
                continue;
            }

            if (! $this->canViewRelatedRecord($record)) {
                return false;
            }
        }

        return true;
    }

    protected function canViewRelatedRecord(Model $record): bool
    {
        if ($record instanceof User) {
            return $this->canAccessUserRecord($record, 'view');
        }

        return $this->allows('view', $record);
    }

    protected function canAccessUserRecord(User $user, string $ability): bool
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

        return Security::can(User::class, $ability, $currentUser);
    }

    /**
     * @return class-string<Model>|null
     */
    protected function relationModelClass(): ?string
    {
        return match ($this->relationTable()) {
            'actions' => Action::class,
            'decisions' => Decision::class,
            'defenders' => Defender::class,
            'engines' => Engine::class,
            'groups' => Group::class,
            'labels' => Label::class,
            'permissions' => Permission::class,
            'principles' => Principle::class,
            'rules' => RuleModel::class,
            'targets' => Target::class,
            'users' => User::class,
            'wordlists' => Wordlist::class,
            default => null,
        };
    }
}
