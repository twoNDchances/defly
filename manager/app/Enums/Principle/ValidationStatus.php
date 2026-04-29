<?php

namespace App\Enums\Principle;

enum ValidationStatus: string
{
    case Pending = 'pending';
    case Validating = 'validating';
    case Failed = 'failed';
    case Passed = 'passed';
}
