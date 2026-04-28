<?php

namespace App\Enums\Defender;

enum DeploymentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Failed = 'failed';
    case Successful = 'successful';
}
