<?php

namespace App\Enums\Engine;

enum Type: string
{
    case IndexOf = 'indexOf';
    case Merge = 'merge';
    case Addition = 'addition';
    case Subtraction = 'subtraction';
    case Multiplication = 'multiplication';
    case Division = 'division';
    case PowerOf = 'powerOf';
    case Remainder = 'remainder';
    case ToString = 'toString';
    case Lower = 'lower';
    case Upper = 'upper';
    case Capitalize = 'capitalize';
    case Trim = 'trim';
    case TrimLeft = 'trimLeft';
    case TrimRight = 'trimRight';
    case RemoveWhitespace = 'removeWhitespace';
    case Length = 'length';
    case Hash = 'hash';
    case Split = 'split';
}
