<?php

namespace App\Enums\Engine;

enum Hash: string
{
    case Md5 = 'md5';
    case Sha1 = 'sha1';
    case Sha224 = 'sha224';
    case Sha256 = 'sha256';
    case Sha512 = 'sha512';
}
