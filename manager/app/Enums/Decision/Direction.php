<?php

namespace App\Enums\Decision;

enum Direction: string
{
    case Request = 'request';
    case Response = 'response';
}
