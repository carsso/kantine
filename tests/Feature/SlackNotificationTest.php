<?php

namespace Tests\Feature;

use App\Jobs\UpdateMenusFromApiJob;
use App\Models\Tenant;
use App\Services\SlackNotificationService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SlackNotificationTest extends TestCase
{
    private const FAILED_WEBHOOK = 'https://hooks.slack.test/failed';

    private const SUCCESS_WEBHOOK = 'https://hooks.slack.test/success';

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

    private function enable(): void
    {
        config([
            'services.slack.notifications_enabled' => true,
            'services.slack.webhook_url_failed' => self::FAILED_WEBHOOK,
            'services.slack.webhook_url_success' => self::SUCCESS_WEBHOOK,
        ]);
    }

    public function test_it_posts_a_message_to_the_failed_webhook(): void
    {
        Http::fake();
        $this->enable();

        app(SlackNotificationService::class)->notifyJobFailed(
            'uuid-1',
            $this->jobPayload(),
            "RuntimeException: boom\n#0 stack trace",
            '2026-08-21 10:00:00'
        );

        Http::assertSent(function (Request $request) {
            return $request->url() === self::FAILED_WEBHOOK
                && str_contains($request['text'], '❌ *Job Failed : UpdateMenusFromApiJob')
                && str_contains($request['text'], '*UUID:* `uuid-1`')
                && str_contains($request['text'], '```RuntimeException: boom```')
                && ! str_contains($request['text'], '#0 stack trace')
                && $request['username'] === config('app.name');
        });
    }

    public function test_it_posts_a_message_to_the_success_webhook(): void
    {
        Http::fake();
        $this->enable();

        app(SlackNotificationService::class)->notifyJobSuccess(
            'uuid-2',
            $this->jobPayload(),
            '2026-08-21 10:00:00'
        );

        Http::assertSent(function (Request $request) {
            return $request->url() === self::SUCCESS_WEBHOOK
                && str_contains($request['text'], '✅ *Job Completed : UpdateMenusFromApiJob')
                && str_contains($request['text'], '*Finished at:* `2026-08-21 10:00:00`');
        });
    }

    public function test_it_resolves_the_tenant_name_from_the_serialized_command(): void
    {
        Http::fake();
        $this->enable();
        $tenant = Tenant::factory()->create(['name' => 'Lille']);

        $payload = $this->jobPayload();
        $payload['data']['command'] = serialize(new UpdateMenusFromApiJob($tenant));

        app(SlackNotificationService::class)->notifyJobSuccess('uuid-3', $payload, '2026-08-21 10:00:00');

        Http::assertSent(fn (Request $request) => str_contains($request['text'], 'Lille (ID: '.$tenant->id.')'));
    }

    public function test_it_falls_back_to_an_unknown_tenant(): void
    {
        Http::fake();
        $this->enable();

        app(SlackNotificationService::class)->notifyJobSuccess('uuid-4', $this->jobPayload(), '2026-08-21 10:00:00');

        Http::assertSent(fn (Request $request) => str_contains($request['text'], 'Inconnu (ID: 0)'));
    }

    public function test_it_does_nothing_when_notifications_are_disabled(): void
    {
        Http::fake();
        $this->enable();
        config(['services.slack.notifications_enabled' => false]);

        $service = app(SlackNotificationService::class);
        $service->notifyJobFailed('uuid-5', $this->jobPayload(), 'boom', '2026-08-21 10:00:00');
        $service->notifyJobSuccess('uuid-5', $this->jobPayload(), '2026-08-21 10:00:00');

        Http::assertNothingSent();
    }

    public function test_it_does_nothing_when_webhook_urls_are_missing(): void
    {
        Http::fake();
        config([
            'services.slack.notifications_enabled' => true,
            'services.slack.webhook_url_failed' => null,
            'services.slack.webhook_url_success' => null,
        ]);

        $service = app(SlackNotificationService::class);
        $service->notifyJobFailed('uuid-6', $this->jobPayload(), 'boom', '2026-08-21 10:00:00');
        $service->notifyJobSuccess('uuid-6', $this->jobPayload(), '2026-08-21 10:00:00');

        Http::assertNothingSent();
    }
}
