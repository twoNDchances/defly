<?php

namespace App\Http\Controllers;

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
}
