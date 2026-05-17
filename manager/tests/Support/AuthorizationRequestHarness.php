<?php

namespace Tests\Support;

use App\Models\User;
use App\Traits\Requests\Authorization as RequestAuthorization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class AuthorizationRequestHarness extends FormRequest
{
    use RequestAuthorization;

    public function setTestUser(?User $user)
    {
        $this->setUserResolver(fn () => $user);
    }

    public function allowsPublic(string $ability, mixed $target): bool
    {
        return $this->allows($ability, $target);
    }

    public function canAccessRecordPublic(Model $model, string $ability): bool
    {
        return $this->canAccessRecord($model, $ability);
    }

    public function paginationRulesPublic(): array
    {
        return $this->paginationRules();
    }

    public function modelDataPublic(Model $model, array $fields): array
    {
        return $this->modelData($model, $fields);
    }

    public function enumValuePublic(mixed $value): mixed
    {
        return $this->enumValue($value);
    }
}
