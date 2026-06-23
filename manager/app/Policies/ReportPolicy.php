<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use App\Services\Security;
use App\Traits\Policies\Basic;

class ReportPolicy
{
    use Basic;

    public function getModel()
    {
        return Report::class;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Report $report): bool
    {
        return false;
    }

    public function review(User $user, Report $report): bool
    {
        if ($report->is_reviewed) {
            return false;
        }

        return $this->checkAccess($user, $report, 'review');
    }

    public function reviewAny(User $user): bool
    {
        return Security::can($this->getModel(), 'reviewAny', $user);
    }
}
