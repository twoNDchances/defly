<?php

namespace App\Traits\Filament\Specifics\Timeline;

use App\Filament\Clusters\AccessControl\Resources\Groups\GroupResource;
use App\Filament\Clusters\AccessControl\Resources\Permissions\PermissionResource;
use App\Filament\Clusters\Authentication\Resources\Keys\KeyResource;
use App\Filament\Clusters\Authentication\Resources\Users\UserResource;
use App\Filament\Clusters\Context\Resources\Engines\EngineResource;
use App\Filament\Clusters\Context\Resources\Patterns\PatternResource;
use App\Filament\Clusters\Context\Resources\Targets\TargetResource;
use App\Filament\Clusters\Context\Resources\Wordlists\WordlistResource;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\DefenderResource;
use App\Filament\Clusters\Infrastructure\Resources\Guards\GuardResource;
use App\Filament\Clusters\Initialization\Resources\Actions\ActionResource;
use App\Filament\Clusters\Initialization\Resources\Decisions\DecisionResource;
use App\Filament\Clusters\Initialization\Resources\Principles\PrincipleResource;
use App\Filament\Clusters\Initialization\Resources\Rules\RuleResource;
use App\Filament\Resources\Labels\LabelResource;
use App\Models\Action;
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
use App\Models\Rule;
use App\Models\Target;
use App\Models\User;
use App\Models\Wordlist;
use App\Traits\Filament\Generals\Components\Column;
use Illuminate\Support\Str;

trait TimelineColumn
{
    use Column, TimelineButton, TimelineData;

    public static function getLoggedAt()
    {
        return self::getCreatedAt()
            ->toggledHiddenByDefault(false);
    }

    public static function getMethod()
    {
        return self::textColumn('method', __('models.timeline.fields.method'))
            ->formatStateUsing(fn ($state) => self::methodOptionsAndColors()['options'][$state])
            ->color(fn ($state) => self::methodOptionsAndColors()['colors'][$state])
            ->badge();
    }

    public static function getPath()
    {
        return self::textColumn('path', __('models.timeline.fields.path'));
    }

    public static function getAction()
    {
        return self::textColumn('action', __('models.timeline.fields.action'))
            ->formatStateUsing(fn ($state) => self::actionOptionsAndColors()['options'][$state] ?? Str::headline((string) $state))
            ->color(fn ($state) => self::actionOptionsAndColors()['colors'][$state] ?? 'gray')
            ->badge();
    }

    public static function getResource()
    {
        $resources = [
            Action::class => ActionResource::class,
            Decision::class => DecisionResource::class,
            Defender::class => DefenderResource::class,
            Engine::class => EngineResource::class,
            Group::class => GroupResource::class,
            Guard::class => GuardResource::class,
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

        return self::textColumn('resource_type', __('models.timeline.fields.resource'))
            ->formatStateUsing(fn ($state) => self::resourceTypeOptionsAndColors()['options'][$state] ?? (blank($state) ? '' : Str::headline(class_basename($state))))
            ->url(function ($record) use ($resources) {
                $resource = $resources[$record->resource_type] ?? null;

                if (! $resource || blank($record->resource_id) || ! class_exists($resource)) {
                    return null;
                }

                return $resource::getUrl('edit', ['record' => $record->resource_id]);
            })
            ->openUrlInNewTab();
    }
}
