<?php

namespace App\Services;

use App\Models\Action;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Engine;
use App\Models\Policy;
use App\Models\Rule;
use App\Models\Target;
use App\Models\Wordlist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Lock
{
    protected const LOCK_COLUMN = 'is_locked';

    /**
     * @var array<class-string<Model>, array<int, array{foreign_key: string, related_model: class-string<Model>}>>
     */
    protected const FOREIGN_KEY_MAP = [
        Target::class => [
            ['foreign_key' => 'wordlist_id', 'related_model' => Wordlist::class],
        ],
        Rule::class => [
            ['foreign_key' => 'target_id', 'related_model' => Target::class],
            ['foreign_key' => 'wordlist_id', 'related_model' => Wordlist::class],
        ],
    ];

    /**
     * @var array<class-string<Model>, array<int, array{type: 'model'|'table', model?: class-string<Model>, table?: string, foreign_key: string}>>
     */
    protected const RELATIONSHIP_MAP = [
        Wordlist::class => [
            ['type' => 'model', 'model' => Target::class, 'foreign_key' => 'wordlist_id'],
            ['type' => 'model', 'model' => Rule::class, 'foreign_key' => 'wordlist_id'],
        ],
        Target::class => [
            ['type' => 'model', 'model' => Rule::class, 'foreign_key' => 'target_id'],
        ],
        Rule::class => [
            ['type' => 'table', 'table' => 'policies_rules', 'foreign_key' => 'rule'],
        ],
        Action::class => [
            ['type' => 'table', 'table' => 'rules_actions', 'foreign_key' => 'action'],
        ],
        Engine::class => [
            ['type' => 'table', 'table' => 'targets_engines', 'foreign_key' => 'engine'],
        ],
        Policy::class => [
            ['type' => 'table', 'table' => 'defenders_policies', 'foreign_key' => 'policy'],
        ],
        Decision::class => [
            ['type' => 'table', 'table' => 'defenders_decisions', 'foreign_key' => 'decision'],
        ],
    ];

    /**
     * @var array<class-string<Model>, array<int, array{table: string, self_key: string, related_model: class-string<Model>, related_key: string}>>
     */
    protected const PIVOT_MAP = [
        Target::class => [
            ['table' => 'targets_engines', 'self_key' => 'target', 'related_model' => Engine::class, 'related_key' => 'engine'],
        ],
        Rule::class => [
            ['table' => 'rules_actions', 'self_key' => 'rule', 'related_model' => Action::class, 'related_key' => 'action'],
            ['table' => 'policies_rules', 'self_key' => 'rule', 'related_model' => Policy::class, 'related_key' => 'policy'],
        ],
        Policy::class => [
            ['table' => 'policies_rules', 'self_key' => 'policy', 'related_model' => Rule::class, 'related_key' => 'rule'],
        ],
        Defender::class => [
            ['table' => 'defenders_policies', 'self_key' => 'defender', 'related_model' => Policy::class, 'related_key' => 'policy'],
            ['table' => 'defenders_decisions', 'self_key' => 'defender', 'related_model' => Decision::class, 'related_key' => 'decision'],
        ],
    ];

    public static function syncByForeignKey(Model $model): void
    {
        if ((! $model->exists) || (! $model->hasAttribute(self::LOCK_COLUMN))) {
            return;
        }

        $foreignKeys = self::FOREIGN_KEY_MAP[$model::class] ?? [];

        foreach ($foreignKeys as $foreignKeyConfig) {
            $foreignKey = $foreignKeyConfig['foreign_key'];
            $relatedModelClass = $foreignKeyConfig['related_model'];
            $oldId = data_get($model->getPrevious(), $foreignKey);
            $newId = $model->getAttribute($foreignKey);

            foreach (self::normalizeIds([$oldId, $newId]) as $id) {
                self::syncById($relatedModelClass, $id);
            }
        }
    }

    /**
     * @param  class-string<Model>  $relatedModelClass
     */
    public static function syncByRelationship(string $relatedModelClass, mixed $ids, ?array $ignore = null): void
    {
        foreach (self::normalizeIds($ids) as $id) {
            self::syncById($relatedModelClass, $id, $ignore);
        }
    }

    public static function syncByDeleting(Model $model): void
    {
        if (blank($model->getKey()) || ! self::hasDeletingRegistration($model::class)) {
            return;
        }

        $ignore = self::buildDeletingIgnoreContext($model);

        foreach (self::FOREIGN_KEY_MAP[$model::class] ?? [] as $foreignKeyConfig) {
            $foreignKey = $foreignKeyConfig['foreign_key'];
            $relatedModelClass = $foreignKeyConfig['related_model'];
            $id = $model->getAttribute($foreignKey);

            if (blank($id)) {
                $id = $model->getOriginal($foreignKey);
            }

            if (blank($id)) {
                continue;
            }

            self::syncByRelationship($relatedModelClass, $id, $ignore);
        }

        foreach (self::PIVOT_MAP[$model::class] ?? [] as $pivotConfig) {
            $relatedIds = DB::table($pivotConfig['table'])
                ->where($pivotConfig['self_key'], $model->getKey())
                ->pluck($pivotConfig['related_key'])
                ->all();

            self::syncByRelationship($pivotConfig['related_model'], $relatedIds, $ignore);
        }
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected static function hasDeletingRegistration(string $modelClass): bool
    {
        return (self::FOREIGN_KEY_MAP[$modelClass] ?? []) !== []
            || (self::PIVOT_MAP[$modelClass] ?? []) !== [];
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected static function syncById(string $modelClass, mixed $id, ?array $ignore = null): void
    {
        if (blank($id)) {
            return;
        }

        /** @var Model|null $record */
        $record = $modelClass::query()->find($id);

        if ((! $record) || (! $record->hasAttribute(self::LOCK_COLUMN))) {
            return;
        }

        $shouldLock = self::isReferenced($record, $ignore);

        if ((bool) data_get($record, self::LOCK_COLUMN) === $shouldLock) {
            return;
        }

        $modelClass::query()
            ->whereKey($id)
            ->update([self::LOCK_COLUMN => $shouldLock]);
    }

    protected static function isReferenced(Model $model, ?array $ignore = null): bool
    {
        $usageDefinitions = self::RELATIONSHIP_MAP[$model::class] ?? [];

        if ($usageDefinitions === []) {
            return false;
        }

        $modelId = $model->getKey();

        foreach ($usageDefinitions as $usage) {
            if (self::hasUsage($usage, $modelId, $ignore)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{type: 'model'|'table', model?: class-string<Model>, table?: string, foreign_key: string}  $usage
     */
    protected static function hasUsage(array $usage, mixed $modelId, ?array $ignore = null): bool
    {
        if ($usage['type'] === 'model') {
            /** @var class-string<Model> $usageModelClass */
            $usageModelClass = $usage['model'];
            $query = $usageModelClass::query()
                ->where($usage['foreign_key'], $modelId);

            $ignoreModelClass = data_get($ignore, 'model.class');
            $ignoreModelId = data_get($ignore, 'model.id');

            if (($usageModelClass === $ignoreModelClass) && filled($ignoreModelId)) {
                $query->where((new $usageModelClass)->getQualifiedKeyName(), '!=', $ignoreModelId);
            }

            return $query->exists();
        }

        $query = DB::table($usage['table'])
            ->where($usage['foreign_key'], $modelId);

        foreach (data_get($ignore, 'pivots', []) as $ignorePivot) {
            if (($ignorePivot['table'] ?? null) !== $usage['table']) {
                continue;
            }

            if (blank($ignorePivot['self_key'] ?? null) || blank($ignorePivot['self_id'] ?? null)) {
                continue;
            }

            $query->where($ignorePivot['self_key'], '!=', $ignorePivot['self_id']);
        }

        return $query->exists();
    }

    /**
     * @return array<int, mixed>
     */
    protected static function normalizeIds(mixed $ids): array
    {
        if ($ids instanceof Model) {
            $ids = [$ids->getKey()];
        } elseif ($ids instanceof Collection) {
            $ids = $ids->all();
        } elseif (! is_array($ids)) {
            $ids = [$ids];
        }

        $normalizedIds = [];

        foreach ($ids as $id) {
            if (blank($id)) {
                continue;
            }

            $normalizedIds[(string) $id] = $id;
        }

        return array_values($normalizedIds);
    }

    /**
     * @return array{
     *     model: array{class: class-string<Model>, id: mixed},
     *     pivots: array<int, array{table: string, self_key: string, self_id: mixed}>
     * }
     */
    protected static function buildDeletingIgnoreContext(Model $model): array
    {
        $ignorePivots = [];

        foreach (self::PIVOT_MAP[$model::class] ?? [] as $pivotConfig) {
            $ignorePivots[] = [
                'table' => $pivotConfig['table'],
                'self_key' => $pivotConfig['self_key'],
                'self_id' => $model->getKey(),
            ];
        }

        return [
            'model' => [
                'class' => $model::class,
                'id' => $model->getKey(),
            ],
            'pivots' => $ignorePivots,
        ];
    }
}
