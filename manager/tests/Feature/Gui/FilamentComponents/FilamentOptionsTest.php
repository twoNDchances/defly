<?php

namespace Tests\Feature\Gui\FilamentComponents;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use ReflectionMethod;
use Tests\Support\FilamentActionDataHarness;
use Tests\Support\FilamentDecisionDataHarness;
use Tests\Support\FilamentDefenderDataHarness;
use Tests\Support\FilamentEngineDataHarness;
use Tests\Support\FilamentGeneralDataHarness;
use Tests\Support\FilamentRuleDataHarness;
use Tests\TestCase;

class FilamentOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_filament_option_description_and_color_helpers_are_resolvable(): void
    {
        $this->actingAs(User::factory()->create([
            'is_root' => true,
            'is_verified' => true,
            'is_activated' => true,
        ]));

        foreach ([
            FilamentActionDataHarness::class,
            FilamentDecisionDataHarness::class,
            FilamentDefenderDataHarness::class,
            FilamentEngineDataHarness::class,
            FilamentGeneralDataHarness::class,
            FilamentRuleDataHarness::class,
        ] as $class) {
            foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->isStatic() && $method->getNumberOfRequiredParameters() === 0
                    && preg_match('/(Options|Descriptions|Colors)/', $method->getName())) {
                    $this->assertIsArray($class::{$method->getName()}(), "{$class}::{$method->getName()} should return an array.");
                }
            }
        }
    }
}
