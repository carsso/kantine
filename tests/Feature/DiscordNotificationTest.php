<?php

namespace Tests\Feature;

use App\Services\DiscordNotificationService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DiscordNotificationTest extends TestCase
{
    private const FAILED_WEBHOOK = 'https://discord.com/api/webhooks/1/failed';

    private const SUCCESS_WEBHOOK = 'https://discord.com/api/webhooks/1/success';

    /**
     * @return array{displayName: string, data: array{command: null}}
     */
    private function jobPayload(): array
    {
        return [
            'displayName' => 'App\\Jobs\\UpdateMenusFromApiJob',
            'data' => ['command' => null],
        ];
    }

    public function test_it_posts_a_message_to_the_failed_webhook(): void
    {
        Http::fake();
        config([
            'services.discord.notifications_enabled' => true,
            'services.discord.webhook_url_failed' => self::FAILED_WEBHOOK,
        ]);

        app(DiscordNotificationService::class)->notifyJobFailed(
            'uuid-1',
            $this->jobPayload(),
            "RuntimeException: boom\n#0 stack trace",
            '2026-08-21 10:00:00'
        );

        Http::assertSent(function (Request $request) {
            return $request->url() === self::FAILED_WEBHOOK
                && str_contains($request['content'], '❌ **Job Failed : UpdateMenusFromApiJob')
                && str_contains($request['content'], '**UUID:** `uuid-1`')
                && str_contains($request['content'], '```RuntimeException: boom```')
                && $request['username'] === config('app.name');
        });
    }

    public function test_it_posts_a_message_to_the_success_webhook(): void
    {
        Http::fake();
        config([
            'services.discord.notifications_enabled' => true,
            'services.discord.webhook_url_success' => self::SUCCESS_WEBHOOK,
        ]);

        app(DiscordNotificationService::class)->notifyJobSuccess(
            'uuid-2',
            $this->jobPayload(),
            '2026-08-21 10:00:00'
        );

        Http::assertSent(function (Request $request) {
            return $request->url() === self::SUCCESS_WEBHOOK
                && str_contains($request['content'], '✅ **Job Completed : UpdateMenusFromApiJob')
                && str_contains($request['content'], '**Finished at:** `2026-08-21 10:00:00`');
        });
    }

    public function test_it_does_nothing_when_notifications_are_disabled(): void
    {
        Http::fake();
        config([
            'services.discord.notifications_enabled' => false,
            'services.discord.webhook_url_failed' => self::FAILED_WEBHOOK,
            'services.discord.webhook_url_success' => self::SUCCESS_WEBHOOK,
        ]);

        $service = app(DiscordNotificationService::class);
        $service->notifyJobFailed('uuid-3', $this->jobPayload(), 'boom', '2026-08-21 10:00:00');
        $service->notifyJobSuccess('uuid-3', $this->jobPayload(), '2026-08-21 10:00:00');

        Http::assertNothingSent();
    }

    public function test_it_does_nothing_when_webhook_urls_are_missing(): void
    {
        Http::fake();
        config([
            'services.discord.notifications_enabled' => true,
            'services.discord.webhook_url_failed' => null,
            'services.discord.webhook_url_success' => null,
        ]);

        $service = app(DiscordNotificationService::class);
        $service->notifyJobFailed('uuid-4', $this->jobPayload(), 'boom', '2026-08-21 10:00:00');
        $service->notifyJobSuccess('uuid-4', $this->jobPayload(), '2026-08-21 10:00:00');

        Http::assertNothingSent();
    }
}
