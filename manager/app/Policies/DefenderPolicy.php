<?php

namespace App\Policies;

use App\Enums\Defender\DeploymentStatus;
use App\Models\Defender;
use App\Models\User;
use App\Services\Security;
use App\Traits\Policies\Basic;

class DefenderPolicy
{
    use Basic;

    public function getModel()
    {
        return Defender::class;
    }

    private const PROTECTED_STATUSES = [
        DeploymentStatus::Pending,
        DeploymentStatus::Deploying,
    ];

    protected function isProtectedStatus(Defender $defender): bool
    {
        return in_array($defender->deployment_status, self::PROTECTED_STATUSES, true);
    }

    public function update(User $user, Defender $defender): bool
    {
        if ($this->isProtectedStatus($defender)) {
            return false;
        }

        return $this->checkAccess($user, $defender, 'update');
    }

    public function delete(User $user, Defender $defender): bool
    {
        if ($this->isProtectedStatus($defender)) {
            return false;
        }

        return $this->checkAccess($user, $defender, 'delete');
    }

    public function deploy(User $user, Defender $defender): bool
    {
        if ($this->isProtectedStatus($defender)) {
            return false;
        }

        return $this->checkAccess($user, $defender, 'deploy');
    }

    public function deployAny(User $user): bool
    {
        return Security::can($this->getModel(), 'deployAny', $user);
    }
}
