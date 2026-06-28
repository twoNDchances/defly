<?php

namespace App\Services;

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
use App\Models\Timeline;
use App\Models\User;
use App\Models\Wordlist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AssistantResource
{
    /**
     * @return array<string, array{model: class-string<Model>, title: string, search: array<int, string>}>
     */
    protected static function definitions(): array
    {
        return [
            'user' => ['model' => User::class, 'title' => 'name', 'search' => ['name', 'email']],
            'group' => ['model' => Group::class, 'title' => 'name', 'search' => ['name']],
            'permission' => ['model' => Permission::class, 'title' => 'name', 'search' => ['name']],
            'key' => ['model' => Key::class, 'title' => 'name', 'search' => ['name']],
            'label' => ['model' => Label::class, 'title' => 'name', 'search' => ['name']],
            'wordlist' => ['model' => Wordlist::class, 'title' => 'name', 'search' => ['name']],
            'engine' => ['model' => Engine::class, 'title' => 'name', 'search' => ['name']],
            'pattern' => ['model' => Pattern::class, 'title' => 'name', 'search' => ['name']],
            'target' => ['model' => Target::class, 'title' => 'name', 'search' => ['name']],
            'action' => ['model' => Action::class, 'title' => 'name', 'search' => ['name']],
            'rule' => ['model' => Rule::class, 'title' => 'name', 'search' => ['name']],
            'principle' => ['model' => Principle::class, 'title' => 'name', 'search' => ['name']],
            'decision' => ['model' => Decision::class, 'title' => 'name', 'search' => ['name']],
            'defender' => ['model' => Defender::class, 'title' => 'name', 'search' => ['name']],
            'timeline' => ['model' => Timeline::class, 'title' => 'path', 'search' => ['path', 'action', 'id']],
        ];
    }

    /** @return array<string, string> */
    public static function typeOptions(): array
    {
        return collect(static::definitions())
            ->filter(fn (array $definition): bool => Security::can($definition['model'], 'viewAny'))
            ->mapWithKeys(fn (array $definition, string $type): array => [
                $type => static::typeLabel($type),
            ])
            ->all();
    }

    public static function typeLabel(string $type): string
    {
        return __("models.{$type}.name");
    }

    /** @return array<string, string> */
    public static function options(string $type, string $search = ''): array
    {
        $definition = static::availableDefinition($type);
        if ($definition === null) {
            return [];
        }

        $query = $definition['model']::query();
        $search = trim($search);

        if ($search !== '') {
            $query->where(function (Builder $query) use ($definition, $search): void {
                foreach ($definition['search'] as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}($column, 'like', "%{$search}%");
                }
            });
        }

        return $query
            ->orderBy($definition['title'])
            ->limit(50)
            ->get()
            ->filter(fn (Model $record): bool => Security::can($record::class, 'view'))
            ->mapWithKeys(fn (Model $record): array => [
                (string) $record->getKey() => static::recordLabel($type, $record),
            ])
            ->all();
    }

    public static function optionLabel(string $type, string $id): ?string
    {
        $record = static::find($type, $id);

        return $record === null ? null : static::recordLabel($type, $record);
    }

    /** @return array{type: string, id: string, label: string}|null */
    public static function reference(string $type, string $id): ?array
    {
        $record = static::find($type, $id);
        if ($record === null) {
            return null;
        }

        return [
            'type' => $type,
            'id' => (string) $record->getKey(),
            'label' => static::recordLabel($type, $record),
        ];
    }

    /**
     * @param  array<int, array{type?: mixed, id?: mixed}>  $references
     * @return array<int, array{type: string, id: string, label: string, data: array<string, mixed>}>
     */
    public static function snapshots(array $references): array
    {
        return collect($references)
            ->map(function (array $reference): ?array {
                $type = (string) ($reference['type'] ?? '');
                $id = (string) ($reference['id'] ?? '');
                $record = static::find($type, $id);

                if ($record === null) {
                    return null;
                }

                return [
                    'type' => $type,
                    'id' => (string) $record->getKey(),
                    'label' => static::recordLabel($type, $record),
                    'data' => $record->toArray(),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{model: class-string<Model>, title: string, search: array<int, string>}|null
     */
    protected static function availableDefinition(string $type): ?array
    {
        $definition = static::definitions()[$type] ?? null;
        if ($definition === null || ! Security::can($definition['model'], 'viewAny')) {
            return null;
        }

        return $definition;
    }

    protected static function find(string $type, string $id): ?Model
    {
        $definition = static::availableDefinition($type);
        if ($definition === null || $id === '') {
            return null;
        }

        $record = $definition['model']::query()->find($id);
        if ($record === null || ! Security::can($record::class, 'view')) {
            return null;
        }

        return $record;
    }

    protected static function recordLabel(string $type, Model $record): string
    {
        if ($record instanceof User) {
            return trim("{$record->name} ({$record->email})");
        }

        if ($record instanceof Timeline) {
            return trim(implode(' - ', array_filter([
                $record->action,
                $record->path,
                (string) $record->getKey(),
            ])));
        }

        $title = static::definitions()[$type]['title'];
        $label = trim((string) data_get($record, $title));

        return $label !== '' ? $label : (string) $record->getKey();
    }
}
