<?php

namespace App\Enums;

enum Type: string
{
    case Getter = 'getter';
    case Full = 'full';
    case Header = 'header';
    case Meta = 'meta';
    case Query = 'query';
    case Body = 'body';
    case File = 'file';
}
