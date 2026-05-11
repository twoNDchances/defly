<?php

namespace App\Services;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class Notification
{
    public const STATUS_DANGER = 'danger';

    public const STATUS_INFO = 'info';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_WARNING = 'warning';

    public static function sendToRequester(
        ?string $requesterEmail,
        string $title,
        ?string $body = null,
        string $status = self::STATUS_INFO,
        ?string $url = null,
        ?string $urlLabel = null,
    ): void {
        $recipient = self::recipientByEmail($requesterEmail);

        if (! $recipient) {
            return;
        }

        self::send($recipient, $title, $body, $status, $url, $urlLabel);
    }

    public static function sendForRecord(
        ?string $requesterEmail,
        ?Model $record,
        string $title,
        ?string $body = null,
        string $status = self::STATUS_INFO,
        ?string $url = null,
        ?string $urlLabel = null,
    ): void {
        $recipient = self::recipientByEmail($requesterEmail)
            ?? self::recipientByRecordOwner($record);

        if (! $recipient) {
            return;
        }

        self::send($recipient, $title, $body, $status, $url, $urlLabel);
    }

    public static function resourceUrl(string $resource, Model $record, string $page = 'edit'): ?string
    {
        if (! method_exists($resource, 'getUrl')) {
            return null;
        }

        try {
            return $resource::getUrl(
                $page,
                ['record' => $record->getKey()],
                isAbsolute: false,
                panel: 'defly-manager',
            );
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    protected static function send(
        User $recipient,
        string $title,
        ?string $body,
        string $status,
        ?string $url,
        ?string $urlLabel,
    ): void {
        $notification = self::withStatus(
            FilamentNotification::make()
                ->title($title)
                ->body($body),
            $status,
        );

        if (filled($url)) {
            $notification->actions([
                Action::make('open')
                    ->label($urlLabel ?? __('notifications.actions.open'))
                    ->url($url)
                    ->markAsRead(),
            ]);
        }

        $notification->sendToDatabase($recipient);
    }

    protected static function withStatus(FilamentNotification $notification, string $status): FilamentNotification
    {
        return match ($status) {
            self::STATUS_DANGER => $notification->danger(),
            self::STATUS_SUCCESS => $notification->success(),
            self::STATUS_WARNING => $notification->warning(),
            default => $notification->info(),
        };
    }

    protected static function recipientByEmail(?string $email): ?User
    {
        if (blank($email)) {
            return null;
        }

        return User::query()
            ->where('email', $email)
            ->first();
    }

    protected static function recipientByRecordOwner(?Model $record): ?User
    {
        $ownerId = $record?->getAttribute('created_by');

        if (blank($ownerId)) {
            return null;
        }

        return User::query()->find($ownerId);
    }
}
