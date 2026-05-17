<?php

namespace Tests\Feature\Api;

use App\Enums\Phase;
use App\Enums\Principle\ValidationStatus;
use App\Jobs\PrincipleValidation;
use App\Models\Principle;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;

class PrincipleValidateControllerTest extends ApiTestCase
{
    public function test_principle_validate_endpoint_queues_validation(): void
    {
        Bus::fake();

        $principle = Principle::query()->create([
            'name' => 'needs-validation-'.Str::lower(Str::random(6)),
            'level' => 1,
            'phase' => Phase::One->value,
            'validation_status' => null,
        ]);

        $this->apiJson('POST', $this->apiRoute('principles', 'validate'), ['principle' => $principle->id])
            ->assertOk()
            ->assertJsonPath('validation_status', ValidationStatus::Pending->value);

        Bus::assertDispatched(PrincipleValidation::class);
    }
}
