<?php

namespace App\Enums\Decision;

enum Condition: string
{
    case LessThanOrEqual = '<=';
    case LessThan = '<';
    case Equal = '=';
    case GreaterThanOrEqual = '>=';
    case GreaterThan = '>';
}
