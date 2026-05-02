<?php

namespace App\Traits\Filament\Specifics\Key;

use App\Traits\Filament\Generals\Components\Column;
use Illuminate\Support\Carbon;

trait KeyColumn
{
    use Column, KeyButton, KeyData;

    public static function getName()
    {
        return self::textColumn('name', __('models.key.fields.name'));
    }

    public static function getExpiredAt()
    {
        return self::datetimeColumn('expired_at', __('models.key.fields.expired_at'))
            ->color(function ($record) {
                $time = Carbon::parse($record->expired_at);

                return match (true) {
                    $time->isPast() => 'danger',
                    $time->diffInHours(now()) <= 24 => 'info',
                    default => 'success',
                };
            })
            ->badge();
    }

    public static function getIsReused()
    {
        return self::booleanColumn('is_reused', __('models.key.fields.is_reused'));
    }
}
