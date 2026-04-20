<?php

namespace App\Enums\Rule;

enum Comparator: string
{
    case Similar = '@similar';
    case Contains = '@contains';
    case Match = '@match';
    case Search = '@search';
    case Equal = '@equal';
    case GreaterThan = '@greaterThan';
    case LessThan = '@lessThan';
    case GreaterThanOrEqual = '@greaterThanOrEqual';
    case LessThanOrEqual = '@lessThanOrEqual';
    case InRange = '@inRange';
    case Mirror = '@mirror';
    case StartsWith = '@startsWith';
    case EndsWith = '@endsWith';
    case Check = '@check';
    case RegExp = '@regExp';
    case CheckRegExp = '@checkRegExp';
}
