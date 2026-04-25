<?php

namespace App\Traits\Filament\Specifics\Decision;

use App\Traits\Filament\Generals\Components\Button;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Http;
use Throwable;

trait DecisionButton
{
    use Button;

    public static function testRequestButton()
    {
        return self::button(
            'test_request_button',
            __('forms.decision.buttons.test_request_button'),
            Heroicon::OutlinedBeaker,
            function ($state, $action) {
                if (blank($state)) {
                    Notification::make()
                        ->warning()
                        ->title(__('forms.decision.buttons.test_request_button_empty'))
                        ->send();

                    $action->failure();

                    return;
                }

                try {
                    Http::timeout(10)->get($state);
                } catch (Throwable $exception) {
                    Notification::make()
                        ->warning()
                        ->title(__('forms.decision.buttons.test_request_button_failed'))
                        ->body($exception->getMessage())
                        ->send();

                    $action->failure();
                }
            },
        )
            ->failureNotification(null)
            ->successNotificationTitle(__('forms.decision.buttons.test_request_button_success'));
    }
}
