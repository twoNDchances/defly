<?php

namespace App\Enums\Policy;

enum ValidationStatus: string
{
    case Pending = 'pending';
    case Validating = 'validating';
    case Failed = 'failed';
    case Passed = 'passed';
}
