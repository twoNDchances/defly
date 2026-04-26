<?php

namespace App\Enums\Defender;

enum DeploymentStatus: string
{
    case Pending = 'pending';
    case Deploying = 'deploying';
    case Failed = 'failed';
    case Successful = 'successful';
}
