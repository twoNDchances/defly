<?php

namespace Tests\Feature\Services;

use App\Enums\Datatype;
use App\Enums\Defender\DeploymentStatus;
use App\Enums\Phase;
use App\Enums\Principle\ValidationStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AuthorizationRequestHarness;
use Tests\Support\DomainTestHelpers;
use Tests\Support\RawDefenderForAuthorization;
use Tests\Support\RawPrincipleForAuthorization;
use Tests\TestCase;

class RequestAuthorizationTraitTest extends TestCase
{
    use DomainTestHelpers;
    use RefreshDatabase;

    public function test_authorization_request_helpers_reject_missing_locked_and_invalid_actionable_records(): void
    {
        $authorizedUser = User::factory()->create([
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);

        $request = AuthorizationRequestHarness::create('/locked', 'GET');
        $request->setTestUser(null);
        $this->assertFalse($request->allowsPublic('viewAny', User::class));

        $request->setTestUser($authorizedUser);
        $lockedTarget = $this->target(Datatype::String->value);
        $lockedTarget->forceFill(['is_locked' => true])->save();
        $this->assertFalse($request->canAccessRecordPublic($lockedTarget->fresh(), 'update'));

        $rawDefender = new RawDefenderForAuthorization();
        $rawDefender->setRawAttributes(['deployment_status' => DeploymentStatus::Pending->value], true);
        $this->assertFalse($request->canAccessRecordPublic($rawDefender, 'deploy'));
        $rawDefender->setRawAttributes(['deployment_status' => DeploymentStatus::Successful->value], true);
        $this->assertFalse($request->canAccessRecordPublic($rawDefender, 'delete'));

        $rawPrinciple = new RawPrincipleForAuthorization();
        $rawPrinciple->setRawAttributes(['validation_status' => ValidationStatus::Pending->value], true);
        $this->assertFalse($request->canAccessRecordPublic($rawPrinciple, 'validate'));

        $this->assertArrayHasKey('per_page', $request->paginationRulesPublic());
        $this->assertSame(Phase::One->value, $request->enumValuePublic(Phase::One));
        $this->assertSame(['phase' => Phase::One->value], $request->modelDataPublic($this->principle(), ['phase']));
    }
}
