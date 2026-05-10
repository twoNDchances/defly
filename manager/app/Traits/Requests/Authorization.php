<?php

namespace App\Traits\Requests;

use App\Enums\Defender\DeploymentStatus;
use App\Enums\Principle\ValidationStatus;
use App\Models\Defender;
use App\Models\Principle;
use App\Models\User;
use App\Services\Security;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;

trait Authorization
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function authorizeResourceRequest(string $modelClass, string $routeKey): bool
    {
        $record = $this->route($routeKey);

        return match (true) {
            $this->isMethod('get') => $record instanceof Model
                ? $this->allows('view', $record)
                : $this->allows('viewAny', $modelClass),
            $this->isMethod('post') => $this->allows('create', $modelClass),
            $this->isMethod('put'),
            $this->isMethod('patch') => $record instanceof Model && $this->allows('update', $record),
            $this->isMethod('delete') => $record instanceof Model && $this->allows('delete', $record),
            default => false,
        };
    }

    protected function allows(string $ability, mixed $target): bool
    {
        $user = $this->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($target instanceof Model && ! $this->canAccessRecord($target, $ability)) {
            return false;
        }

        $modelClass = $target instanceof Model ? $target::class : $target;

        return Security::can($modelClass, $ability, $user);
    }

    protected function canAccessRecord(Model $model, string $ability): bool
    {
        if (in_array($ability, ['update', 'delete', 'validate'], true) && data_get($model, 'is_locked') === true) {
            return false;
        }

        if ($model instanceof Defender) {
            $status = $model->deployment_status;

            if (is_string($status)) {
                $status = DeploymentStatus::tryFrom($status);
            }

            if (in_array($ability, ['update', 'delete', 'deploy'], true)
                && in_array($status, [DeploymentStatus::Pending, DeploymentStatus::Processing], true)) {
                return false;
            }

            if ($ability === 'delete' && $status === DeploymentStatus::Successful) {
                return false;
            }
        }

        if ($model instanceof Principle) {
            $status = $model->validation_status;

            if (is_string($status)) {
                $status = ValidationStatus::tryFrom($status);
            }

            if (in_array($ability, ['update', 'delete', 'validate'], true)
                && in_array($status, [ValidationStatus::Pending, ValidationStatus::Validating], true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    protected function paginationRules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @param  array<int, string>  $fields
     * @return array<string, mixed>
     */
    protected function modelData(Model $model, array $fields): array
    {
        $data = [];

        foreach ($fields as $field) {
            $data[$field] = $this->enumValue($model->getAttribute($field));
        }

        return $data;
    }

    protected function enumValue(mixed $value): mixed
    {
        return $value instanceof BackedEnum ? $value->value : $value;
    }
}
