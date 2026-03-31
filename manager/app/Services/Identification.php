<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class Identification
{
    public static function getCurrent()
    {
        return Auth::user();
    }

    public static function getId()
    {
        return self::getCurrent()?->id;
    }

    public static function getEmail()
    {
        return self::getCurrent()?->email;
    }

    public static function getName()
    {
        return self::getCurrent()?->name;
    }

    public static function isRoot()
    {
        return self::getCurrent()?->is_root;
    }

    public static function isActivated()
    {
        return self::getCurrent()?->is_activated;
    }

    public static function isVerified()
    {
        return self::getCurrent()?->is_verified;
    }

    public static function getUsers()
    {
        return self::getCurrent()?->getUsers;
    }

    public static function getCreatedBy()
    {
        return self::getCurrent()?->createdBy;
    }
}
