<?php

namespace App\Traits\Filament\Specifics\Timeline;

use App\Models\Action;
use App\Models\Conservation;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Engine;
use App\Models\Group;
use App\Models\Guard;
use App\Models\Key;
use App\Models\Label;
use App\Models\Pattern;
use App\Models\Permission;
use App\Models\Principle;
use App\Models\Report;
use App\Models\Rule;
use App\Models\Target;
use App\Models\User;
use App\Models\Wordlist;
use App\Traits\Filament\Specifics\GeneralData;

trait TimelineData
{
    use GeneralData;

    public static function actionOptionsAndColors()
    {
        return [
            'options' => [
                'create' => __('models.timeline.extras.action.create'),
                'update' => __('models.timeline.extras.action.update'),
                'delete' => __('models.timeline.extras.action.delete'),
                'clone' => __('models.timeline.extras.action.clone'),
                'validate' => __('models.timeline.extras.action.validate'),
                'deploy' => __('models.timeline.extras.action.deploy'),
                'cancel' => __('models.timeline.extras.action.cancel'),
                'follow' => __('models.timeline.extras.action.follow'),
                'refresh' => __('models.timeline.extras.action.refresh'),
                'apply' => __('models.timeline.extras.action.apply'),
                'revoke' => __('models.timeline.extras.action.revoke'),
                'implement' => __('models.timeline.extras.action.implement'),
                'suspend' => __('models.timeline.extras.action.suspend'),
                'review' => __('models.timeline.extras.action.review'),
            ],
            'colors' => [
                'create' => 'success',
                'update' => 'info',
                'delete' => 'danger',
                'clone' => 'gray',
                'validate' => 'cyan',
                'deploy' => 'teal',
                'cancel' => 'pink',
                'follow' => 'sky',
                'refresh' => 'rose',
                'apply' => 'sky',
                'revoke' => 'pink',
                'implement' => 'orange',
                'suspend' => 'warning',
                'review' => 'success',
            ],
        ];
    }

    public static function resourceTypeOptionsAndColors()
    {
        return [
            'options' => [
                Action::class => __('models.action.name'),
                Conservation::class => __('models.conservation.name'),
                Decision::class => __('models.decision.name'),
                Defender::class => __('models.defender.name'),
                Engine::class => __('models.engine.name'),
                Group::class => __('models.group.name'),
                Guard::class => __('models.guard.name'),
                Key::class => __('models.key.name'),
                Label::class => __('models.label.name'),
                Pattern::class => __('models.pattern.name'),
                Permission::class => __('models.permission.name'),
                Principle::class => __('models.principle.name'),
                Report::class => __('models.report.name'),
                Rule::class => __('models.rule.name'),
                Target::class => __('models.target.name'),
                User::class => __('models.user.name'),
                Wordlist::class => __('models.wordlist.name'),
            ],
            'colors' => [
                Action::class => 'warning',
                Conservation::class => 'purple',
                Decision::class => 'orange',
                Defender::class => 'rose',
                Engine::class => 'sky',
                Group::class => 'info',
                Guard::class => 'slate',
                Key::class => 'purple',
                Label::class => 'teal',
                Pattern::class => 'gray',
                Permission::class => 'indigo',
                Principle::class => 'cyan',
                Report::class => 'pink',
                Rule::class => 'danger',
                Target::class => 'blue',
                User::class => 'success',
                Wordlist::class => 'emerald',
            ],
        ];
    }
}
