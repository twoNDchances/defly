<?php

namespace App\Traits\Filament\Specifics\Timeline;

use App\Traits\Filament\Generals\Components\Field;
use Filament\Support\Icons\Heroicon;

trait TimelineField
{
    use Field, TimelineButton, TimelineData;

    public static function setCreatedAt()
    {
        return self::datetimePicker('created_at', __('models.generals.bases.created_at'))
            ->helperText(__('forms.timeline.descriptions.created_at'))
            ->prefixIcon(Heroicon::OutlinedClock);
    }

    public static function setCreatedBy()
    {
        return self::select('created_by', __('models.generals.bases.created_by'))
            ->relationship('createdBy', 'email')
            ->helperText(__('forms.timeline.descriptions.created_by'))
            ->prefixIcon(Heroicon::OutlinedUser);
    }

    public static function setIpv4()
    {
        return self::textInput('ipv4', __('models.timeline.fields.ipv4'))
            ->helperText(__('forms.timeline.descriptions.ipv4'));
    }

    public static function setIpv6()
    {
        return self::textInput('ipv6', __('models.timeline.fields.ipv6'))
            ->helperText(__('forms.timeline.descriptions.ipv6'));
    }

    public static function setMethod()
    {
        return self::toggleButtons(
            'method',
            __('models.timeline.fields.method'),
            self::methodOptionsAndColors(),
        )
            ->helperText(__('forms.timeline.descriptions.method'));
    }

    public static function setPath()
    {
        return self::textInput('path', __('models.timeline.fields.path'))
            ->helperText(__('forms.timeline.descriptions.path'))
            ->prefixIcon(Heroicon::OutlinedGlobeAlt);
    }

    public static function setAction()
    {
        return self::toggleButtons(
            'action',
            __('models.timeline.fields.action'),
            self::actionOptionsAndColors(),
        )
            ->helperText(__('forms.timeline.descriptions.action'));
    }

    public static function setResourceType()
    {
        return self::toggleButtons(
            'resource_type',
            __('models.timeline.extras.resource.resource_type'),
            self::resourceTypeOptionsAndColors(),
        )
            ->helperText(__('forms.timeline.extras.resource.resource_type'));
    }

    public static function setResourceId()
    {
        return self::textInput(
            'resource_id',
            __('models.timeline.extras.resource.resource_id'),
        )
            ->helperText(__('forms.timeline.extras.resource.resource_id'))
            ->suffixAction(self::openResourceButton());
    }
}
