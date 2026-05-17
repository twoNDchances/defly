<?php

namespace Tests\Feature\Api;

use Tests\Support\ApiRelationTestHelpers;

class PolicyRelationEndpointsTest extends ApiTestCase
{
    use ApiRelationTestHelpers;

    public function test_rule_principle_target_and_defender_relations_are_constrained_and_synced(): void
    {
        $label = $this->label('policy');
        $engine = $this->engine();
        $target = $this->target();
        $action = $this->action();
        $principle = $this->principle();
        $decision = $this->decision();
        $defender = $this->apiDefender();

        $this->attachListDetach('targets.engines', ['target' => $target->id], $engine->id);
        $rule = $this->rule($target);
        $this->attachListDetach('rules.actions', ['rule' => $rule->id], $action->id);
        $this->attachListDetach('rules.labels', ['rule' => $rule->id], $label->id);
        $this->attachListDetach('principles.rules', ['principle' => $principle->id], $rule->id);
        $this->attachListDetach('principles.labels', ['principle' => $principle->id], $label->id);
        $this->attachListDetach('defenders.principles', ['defender' => $defender->id], $principle->id);
        $this->attachListDetach('defenders.decisions', ['defender' => $defender->id], $decision->id);
        $this->attachListDetach('defenders.labels', ['defender' => $defender->id], $label->id);
    }
}
