<?php

namespace App\Services;

use Filament\Actions\Action;
use Filament\Notifications\Notification;

/**
 * KotKompas branded Filament notifications.
 *
 * Usage:
 *   FilamentNotificationService::success();
 *   FilamentNotificationService::success('Kot opgeslagen', 'Je kot is aangemaakt.', icon: 'heroicon-o-home');
 *   FilamentNotificationService::warning(emoji: '⚠️');
 *   FilamentNotificationService::danger('Verwijderen mislukt', icon: 'heroicon-o-trash');
 *   FilamentNotificationService::info('Tip', icon: 'heroicon-o-light-bulb');
 *
 * Icon vs emoji:
 *   - icon  → Heroicon name (e.g. 'heroicon-o-home'). Renders in Filament's icon
 *             slot left of the title, coloured to match the notification status.
 *   - emoji → Plain text prepended to the title string. Use when you want a
 *             colourful glyph without a Heroicon equivalent.
 *   Both can be used together, but usually one or the other is enough.
 */
class FilamentNotificationService
{
    /**
     * Send a success notification.
     */
    public static function success(
        ?string $title = null,
        ?string $body = null,
        ?string $emoji = null,
        ?string $icon = null,
        ?array $actions = null,
    ): void {
        static::send($title, $body, $emoji, $icon, 'success', $actions);
    }

    /**
     * Send a warning notification.
     */
    public static function warning(
        ?string $title = null,
        ?string $body = null,
        ?string $emoji = null,
        ?string $icon = null,
        ?array $actions = null,
    ): void {
        static::send($title, $body, $emoji, $icon, 'warning', $actions);
    }

    /**
     * Send a danger/error notification.
     */
    public static function danger(
        ?string $title = null,
        ?string $body = null,
        ?string $emoji = null,
        ?string $icon = null,
        ?array $actions = null,
    ): void {
        static::send($title, $body, $emoji, $icon, 'danger', $actions);
    }

    /**
     * Send an info notification.
     */
    public static function info(
        ?string $title = null,
        ?string $body = null,
        ?string $emoji = null,
        ?string $icon = null,
        ?array $actions = null,
    ): void {
        static::send($title, $body, $emoji, $icon, 'info', $actions);
    }

    /**
     * Core send method. Called by the convenience methods above.
     *
     * @param  string  $status  'success' | 'warning' | 'danger' | 'info'
     * @param  array<Action>|null  $actions  Optional action buttons (e.g. a link to another page).
     */
    public static function send(
        ?string $title = null,
        ?string $body = null,
        ?string $emoji = null,
        ?string $icon = null,
        string $status = 'success',
        ?array $actions = null,
    ): void {
        $resolvedTitle = static::buildTitle($title, $emoji, $status);

        $notification = Notification::make()
            ->title($resolvedTitle)
            ->status($status);

        if ($body !== null) {
            $notification->body($body);
        }

        // Custom icon overrides the status icon; colour stays tied to status
        // so it always matches the KotKompas palette from DashboardPanelProvider.
        if ($icon !== null) {
            $notification->icon($icon)->iconColor($status);
        }

        if ($actions !== null) {
            $notification->actions($actions);
        }

        $notification->send();
    }

    // -------------------------------------------------------------------------

    /**
     * Build the notification title: prepend emoji when given, fall back to a
     * sensible Dutch default when no custom title is provided.
     */
    protected static function buildTitle(?string $title, ?string $emoji, string $status): string
    {
        $text = $title ?? static::defaultTitle($status);

        return $emoji !== null
            ? $emoji.' '.$text
            : $text;
    }

    /**
     * Dutch default titles per status — used when the caller provides no title.
     */
    protected static function defaultTitle(string $status): string
    {
        return match ($status) {
            'success' => 'Gelukt',
            'warning' => 'Opgelet',
            'danger' => 'Er is iets misgegaan',
            'info' => 'Info',
            default => 'Melding',
        };
    }
}
