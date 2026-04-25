<?php

namespace App\Enums\Decision;

enum Action: string
{
    case Allow = 'allow';
    case Deny = 'deny';
    case RewriteHeaders = 'rewrite_headers';
    case RewriteBody = 'rewrite_body';
    case Redirect = 'redirect';
    case Cancel = 'cancel';
    case Rewrite = 'rewrite';
    case Save = 'save';
    case EraseCookies = 'erase_cookies';
    case ForceNoCache = 'force_no_cache';
}
