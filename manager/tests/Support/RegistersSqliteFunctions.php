<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;

trait RegistersSqliteFunctions
{
    protected function registerSqliteJsonUnquoteFunction()
    {
        $callback = fn ($value) => is_string($value) ? trim($value, '"') : $value;
        $pdo = DB::connection()->getPdo();

        if (method_exists($pdo, 'createFunction')) {
            $pdo->createFunction('JSON_UNQUOTE', $callback);

            return;
        }

        $legacyCreateFunction = 'sqlite'.'CreateFunction';
        $pdo->{$legacyCreateFunction}('JSON_UNQUOTE', $callback);
    }
}
