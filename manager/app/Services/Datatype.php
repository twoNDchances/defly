<?php

namespace App\Services;

use App\Enums\Datatype as DatatypeEnum;
use App\Models\Target;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class Datatype
{
    public static function getFinal(Target $target): ?string
    {
        $currentDatatype = self::normalizeDatatype($target->datatype);

        if ($currentDatatype === null) {
            return null;
        }

        foreach (self::resolveOrderedEngines($target) as $engine) {
            $inputDatatype = self::normalizeDatatype($engine->input_datatype);
            $outputDatatype = self::normalizeDatatype($engine->output_datatype);

            if (($inputDatatype === null) || ($outputDatatype === null)) {
                break;
            }

            if ($inputDatatype !== $currentDatatype) {
                break;
            }

            $currentDatatype = $outputDatatype;
        }

        return $currentDatatype;
    }

    public static function traceBack(iterable $targets): array
    {
        if ($targets instanceof EloquentCollection) {
            $targets->loadMissing('engines');
        }

        $status = true;
        $details = [];

        foreach ($targets as $target) {
            if (! $target instanceof Target) {
                continue;
            }

            $targetTrace = self::traceTarget($target);
            $targetKey = $target->id ?: ('target_'.spl_object_id($target));
            $details[$targetKey] = $targetTrace;

            if (! $targetTrace['status']) {
                $status = false;
            }
        }

        return [
            'status' => $status,
            'details' => $details,
        ];
    }

    protected static function traceTarget(Target $target): array
    {
        $initialDatatype = self::normalizeDatatype($target->datatype);
        $currentDatatype = $initialDatatype;
        $isBroken = false;

        $validEngines = [];
        $invalidEngines = [];

        foreach (self::resolveOrderedEngines($target) as $engineIndex => $engine) {
            $inputDatatype = self::normalizeDatatype($engine->input_datatype);
            $outputDatatype = self::normalizeDatatype($engine->output_datatype);
            $engineKey = $engine->id ?: ('engine_'.$engineIndex);

            $engineDetail = [
                'id' => $engine->id,
                'name' => $engine->name,
                'order' => $engine->pivot?->order,
                'input_datatype' => $inputDatatype,
                'output_datatype' => $outputDatatype,
                'expected_input_datatype' => $currentDatatype,
            ];

            if ($isBroken) {
                $engineDetail['reason'] = 'previous_mismatch';
                $invalidEngines[$engineKey] = $engineDetail;

                continue;
            }

            if (($inputDatatype === null) || ($outputDatatype === null)) {
                $engineDetail['reason'] = 'missing_datatype';
                $invalidEngines[$engineKey] = $engineDetail;
                $isBroken = true;

                continue;
            }

            if ($inputDatatype !== $currentDatatype) {
                $engineDetail['reason'] = 'input_mismatch';
                $invalidEngines[$engineKey] = $engineDetail;
                $isBroken = true;

                continue;
            }

            $engineDetail['reason'] = 'ok';
            $validEngines[$engineKey] = $engineDetail;
            $currentDatatype = $outputDatatype;
        }

        return [
            'status' => count($invalidEngines) === 0,
            'target' => [
                'id' => $target->id,
                'name' => $target->name,
                'initial_datatype' => $initialDatatype,
                'final_datatype' => self::getFinal($target),
            ],
            'engines' => [
                'valid' => $validEngines,
                'invalid' => $invalidEngines,
            ],
        ];
    }

    protected static function resolveOrderedEngines(Target $target): Collection
    {
        $engines = $target->relationLoaded('engines')
            ? $target->engines
            : $target->engines()->get();

        $indexedEngines = $engines
            ->values()
            ->map(fn ($engine, $index) => [
                'engine' => $engine,
                'index' => $index,
                'order' => $engine->pivot?->order,
            ]);

        if ($indexedEngines->every(fn ($item) => $item['order'] === null)) {
            return $indexedEngines
                ->sortBy('index')
                ->pluck('engine')
                ->values();
        }

        return $indexedEngines
            ->sort(function ($a, $b) {
                $aOrder = $a['order'];
                $bOrder = $b['order'];

                if (($aOrder === null) && ($bOrder === null)) {
                    return $a['index'] <=> $b['index'];
                }

                if ($aOrder === null) {
                    return 1;
                }

                if ($bOrder === null) {
                    return -1;
                }

                return ($aOrder <=> $bOrder) ?: ($a['index'] <=> $b['index']);
            })
            ->pluck('engine')
            ->values();
    }

    protected static function normalizeDatatype($datatype): ?string
    {
        if ($datatype instanceof DatatypeEnum) {
            return $datatype->value;
        }

        if (is_string($datatype) && ($datatype !== '')) {
            return $datatype;
        }

        return null;
    }
}
