<?php

namespace App\Traits\Filament\Specifics\Timeline;

use App\Filament\Clusters\AccessControl\Resources\Groups\GroupResource;
use App\Filament\Clusters\AccessControl\Resources\Permissions\PermissionResource;
use App\Filament\Clusters\Authentication\Resources\Keys\KeyResource;
use App\Filament\Clusters\Authentication\Resources\Users\UserResource;
use App\Filament\Clusters\Context\Resources\Engines\EngineResource;
use App\Filament\Clusters\Context\Resources\Patterns\PatternResource;
use App\Filament\Clusters\Context\Resources\Targets\TargetResource;
use App\Filament\Clusters\Initialization\Resources\Actions\ActionResource;
use App\Filament\Clusters\Initialization\Resources\Decisions\DecisionResource;
use App\Filament\Clusters\Initialization\Resources\Principles\PrincipleResource;
use App\Filament\Clusters\Initialization\Resources\Rules\RuleResource;
use App\Filament\Resources\Defenders\DefenderResource;
use App\Filament\Resources\Labels\LabelResource;
use App\Filament\Resources\Wordlists\WordlistResource;
use App\Models\Action;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Engine;
use App\Models\Group;
use App\Models\Key;
use App\Models\Label;
use App\Models\Pattern;
use App\Models\Permission;
use App\Models\Principle;
use App\Models\Rule;
use App\Models\Target;
use App\Models\User;
use App\Models\Wordlist;
use App\Services\Identification;
use App\Traits\Filament\Generals\Components\Button;
use Filament\Support\Icons\Heroicon;

trait TimelineButton
{
    use Button;

    public static function deleteTimelineBulkButton()
    {
        return self::deleteBulkButton()
            ->visible(fn ($livewire): bool => ($livewire->activeTab ?? null) !== 'all'
                || Identification::isRoot());
    }

    public static function openResourceButton()
    {
        $resources = [
            Action::class => ActionResource::class,
            Decision::class => DecisionResource::class,
            Defender::class => DefenderResource::class,
            Engine::class => EngineResource::class,
            Group::class => GroupResource::class,
            Key::class => KeyResource::class,
            Label::class => LabelResource::class,
            Pattern::class => PatternResource::class,
            Permission::class => PermissionResource::class,
            Principle::class => PrincipleResource::class,
            Rule::class => RuleResource::class,
            Target::class => TargetResource::class,
            User::class => UserResource::class,
            Wordlist::class => WordlistResource::class,
        ];

        return self::button(
            'open_resource_button',
            __('forms.timeline.buttons.open_resource'),
            Heroicon::OutlinedArrowTopRightOnSquare,
        )
            ->visible(function ($get) use ($resources): bool {
                $resourceType = $get('resource_type');
                $resourceId = $get('resource_id');

                return filled($resourceType)
                    && filled($resourceId)
                    && isset($resources[$resourceType])
                    && class_exists($resources[$resourceType]);
            })
            ->url(function ($get) use ($resources): ?string {
                $resourceType = $get('resource_type');
                $resourceId = $get('resource_id');
                $resource = $resources[$resourceType] ?? null;

                if (! $resource || blank($resourceId) || ! class_exists($resource)) {
                    return null;
                }

                return $resource::getUrl('edit', ['record' => (string) $resourceId]);
            })
            ->openUrlInNewTab();
    }
}
