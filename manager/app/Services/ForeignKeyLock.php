<?php

namespace App\Services;

use App\Models\Action;
use App\Models\Engine;
use App\Models\Rule;
use App\Models\Target;
use App\Models\Wordlist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ForeignKeyLock
{
    /**
     * @var array<class-string<Model>, array<int, array{type: 'model'|'table', model?: class-string<Model>, table?: string, foreign_key: string}>>
     */
    protected const FK_USAGE_MAP = [
        Wordlist::class => [
            ['type' => 'model', 'model' => Target::class, 'foreign_key' => 'wordlist_id'],
            ['type' => 'model', 'model' => Rule::class, 'foreign_key' => 'wordlist_id'],
        ],
        Target::class => [
            ['type' => 'model', 'model' => Rule::class, 'foreign_key' => 'target_id'],
            ['type' => 'table', 'table' => 'targets_engines', 'foreign_key' => 'target'],
        ],
        Rule::class => [
            ['type' => 'table', 'table' => 'rules_actions', 'foreign_key' => 'rule'],
        ],
        Action::class => [
            ['type' => 'table', 'table' => 'rules_actions', 'foreign_key' => 'action'],
        ],
        Engine::class => [
            ['type' => 'table', 'table' => 'targets_engines', 'foreign_key' => 'engine'],
        ],
    ];

    /**
     * @param  class-string<Model>  $relatedModelClass
     */
    public static function syncOnForeignKeyChange(Model $model, string $foreignKey, string $relatedModelClass): void
    {
        $oldId = $model->getOriginal($foreignKey);
        $newId = $model->getAttribute($foreignKey);

        if ($oldId === $newId) {
            return;
        }

        if (filled($oldId)) {
            self::syncById($relatedModelClass, $oldId);
        }

        if (filled($newId)) {
            self::syncById($relatedModelClass, $newId);
        }
    }

    /**
     * @param  array<string, class-string<Model>>  $foreignKeys
     */
    public static function syncForForeignKeys(Model $model, array $foreignKeys): void
    {
        foreach ($foreignKeys as $foreignKey => $relatedModelClass) {
            self::syncOnForeignKeyChange($model, $foreignKey, $relatedModelClass);
        }
    }

    public static function syncModel(Model $model): void
    {
        if ((! $model->exists) || (! $model->hasAttribute('locked'))) {
            return;
        }

        self::syncById($model::class, $model->getKey());
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public static function syncById(string $modelClass, mixed $id): void
    {
        if (blank($id)) {
            return;
        }

        /** @var Model|null $record */
        $record = $modelClass::query()->find($id);

        if ((! $record) || (! $record->hasAttribute('locked'))) {
            return;
        }

        $shouldLock = self::isReferenced($record);

        if ((bool) $record->locked === $shouldLock) {
            return;
        }

        $modelClass::query()
            ->whereKey($id)
            ->update(['locked' => $shouldLock]);
    }

    public static function isReferenced(Model $model): bool
    {
        $usageDefinitions = self::FK_USAGE_MAP[$model::class] ?? [];

        if ($usageDefinitions === []) {
            return false;
        }

        $modelId = $model->getKey();

        foreach ($usageDefinitions as $usage) {
            if (self::hasUsage($usage, $modelId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{type: 'model'|'table', model?: class-string<Model>, table?: string, foreign_key: string}  $usage
     */
    protected static function hasUsage(array $usage, mixed $modelId): bool
    {
        if ($usage['type'] === 'model') {
            /** @var class-string<Model> $usageModelClass */
            $usageModelClass = $usage['model'];

            return $usageModelClass::query()
                ->where($usage['foreign_key'], $modelId)
                ->exists();
        }

        return DB::table($usage['table'])
            ->where($usage['foreign_key'], $modelId)
            ->exists();
    }
}
