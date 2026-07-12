<?php

namespace Tests\Feature\Observers;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Rule\Comparator;
use App\Enums\Type as TargetType;
use App\Models\Pattern;
use App\Models\Rule;
use App\Models\Target;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\ModelTestHelpers;
use Tests\TestCase;

class TargetAndRuleObserverTest extends TestCase
{
    use ModelTestHelpers;
    use RefreshDatabase;

    public function test_target_and_rule_observers_normalize_dependent_fields(): void
    {
        $wordlist = $this->modelWordlist();
        $pattern = Pattern::query()->create([
            'name' => 'normalizing-pattern-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'datatype' => Datatype::String->value,
        ]);

        $target = Target::query()->create([
            'name' => 'normalizing-target-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'datatype' => Datatype::Number->value,
            'pattern_id' => $pattern->id,
            'wordlist_id' => $wordlist->id,
        ]);
        $target->refresh();

        $this->assertSame(Datatype::String, $target->datatype);
        $this->assertNull($target->wordlist_id);

        $getter = Target::query()->create([
            'name' => 'getter-target-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Getter->value,
            'datatype' => Datatype::String->value,
            'pattern_id' => $pattern->id,
        ]);
        $getter->refresh();
        $this->assertNull($getter->pattern_id);

        $rule = Rule::query()->create([
            'name' => 'normalizing-rule-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'target_id' => $getter->id,
            'comparator' => Comparator::Mirror->value,
            'is_inversed' => false,
            'wordlist_id' => $wordlist->id,
            'configurations' => ['string' => 'needle'],
        ]);
        $rule->refresh();
        $this->assertNull($rule->wordlist_id);
    }
}
