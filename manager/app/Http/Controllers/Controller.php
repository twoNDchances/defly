<?php

namespace App\Http\Controllers;

use App\Services\Lock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function perPage(Request $request): int
    {
        return max(1, min((int) $request->integer('per_page', 15), 100));
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $fields
     * @return array<string, mixed>
     */
    protected function onlyFields(array $data, array $fields): array
    {
        return array_intersect_key($data, array_flip($fields));
    }

    /**
     * @param  class-string<Model>  $relatedModelClass
     * @param  array<int, mixed>  $relatedIds
     */
    protected function syncRelationLocks(string $relatedModelClass, array $relatedIds): void
    {
        Lock::syncByRelationship($relatedModelClass, $relatedIds);
    }
}
