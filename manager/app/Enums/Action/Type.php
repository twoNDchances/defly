<?php

namespace App\Enums\Action;

enum Type: string
{
    case Allow = 'allow';
    case Deny = 'deny';
    case Log = 'log';
    case Request = 'request';
    case Report = 'report';
    case Suspect = 'suspect';
    case Setter = 'setter';
    case Score = 'score';
    case Level = 'level';
}
