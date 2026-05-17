<?php

namespace Tests\Feature\Api;

use Tests\Support\ApiRelationTestHelpers;

class LabelResourceRelationEndpointsTest extends ApiTestCase
{
    use ApiRelationTestHelpers;

    public function test_resource_label_relations_can_be_attached_listed_and_detached_from_both_sides(): void
    {
        $label = $this->label('resources');
        $records = [
            'actions' => ['params' => ['action' => $this->action()->id], 'labelRoute' => 'labels.actions'],
            'decisions' => ['params' => ['decision' => $this->decision()->id], 'labelRoute' => 'labels.decisions'],
            'defenders' => ['params' => ['defender' => $this->apiDefender()->id], 'labelRoute' => 'labels.defenders'],
            'engines' => ['params' => ['engine' => $this->engine()->id], 'labelRoute' => 'labels.engines'],
            'principles' => ['params' => ['principle' => $this->principle()->id], 'labelRoute' => 'labels.principles'],
            'rules' => ['params' => ['rule' => $this->rule()->id], 'labelRoute' => 'labels.rules'],
            'targets' => ['params' => ['target' => $this->target()->id], 'labelRoute' => 'labels.targets'],
            'wordlists' => ['params' => ['wordlist' => $this->wordlist()->id], 'labelRoute' => 'labels.wordlists'],
        ];

        foreach ($records as $resource => $case) {
            $this->attachListDetach("{$resource}.labels", $case['params'], $label->id);
            $resourceId = array_values($case['params'])[0];
            $this->attachListDetach($case['labelRoute'], ['label' => $label->id], $resourceId);
        }
    }
}
