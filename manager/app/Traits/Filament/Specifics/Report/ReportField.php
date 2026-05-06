<?php

namespace App\Traits\Filament\Specifics\Report;

use App\Traits\Filament\Generals\Components\Field;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

trait ReportField
{
    use Field, ReportButton, ReportData;

    public static function setMetaIp()
    {
        return self::textInput('metas.ip', __('models.report.extras.metas.ip'));
    }

    public static function setMetaProtocol()
    {
        return self::textInput('metas.protocol', __('models.report.extras.metas.protocol'));
    }

    public static function setMetaStatus()
    {
        return self::select('metas.status', __('models.report.extras.metas.status'))
            ->options(
                collect(Response::$statusTexts)
                    ->mapWithKeys(fn ($status, $code) => [$code => "[$code] $status"])
                    ->toArray()
            );
    }

    public static function setMetaMethod()
    {
        return self::toggleButtons('metas.method', __('models.report.extras.metas.method'), self::methodOptionsAndColors())
            ->formatStateUsing(fn ($state) => Str::lower($state));
    }

    public static function setMetaUrl()
    {
        return self::textInput('metas.url', __('models.report.extras.metas.url'))
            ->prefixIcon(Heroicon::OutlinedLink);
    }

    public static function setTriggeredBy()
    {
        return self::select('triggered_by', __('models.report.fields.triggered_by'))
            ->relationship('triggeredBy', 'name')
            ->prefixIcon(Heroicon::OutlinedFire);
    }

    public static function setRequestHeaders()
    {
        return self::repeater(
            'request_headers',
            __('models.report.fields.request_headers'),
            'key',
            [
                self::textInput('key', __('models.report.extras.key')),
                self::textArea('value', __('models.report.extras.value')),
            ],
        )
            ->collapsed();
    }

    public static function setResponseHeaders()
    {
        return self::repeater(
            'response_headers',
            __('models.report.fields.response_headers'),
            'key',
            [
                self::textInput('key', __('models.report.extras.key')),
                self::textArea('value', __('models.report.extras.value')),
            ],
        )
            ->collapsed();
    }

    public static function setRequestBody()
    {
        return self::jsonPreview('request_body', __('models.report.fields.request_body'));
    }

    public static function setResponseBody()
    {
        return self::jsonPreview('response_body', __('models.report.fields.response_body'));
    }

    public static function setRuleDetails()
    {
        return self::jsonPreview('rule_details', __('models.report.fields.rule_details'));
    }
}
