<?php

namespace Tests\Feature;

use App\Events\JobFailed;
use App\Events\JobSuccessfullyProcessed;
use App\Models\FailedJob;
use App\Models\SuccessfulJob;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JobEventListenersTest extends TestCase
{
    private const PAYLOAD = '{"displayName":"App\\\\Jobs\\\\UpdateMenusFromApiJob","data":{"command":null}}';

    /**
     * @return array<int, array{message: string, level: string, data: array<mixed>, date: string}>
     */
    private function logs(): array
    {
        return [
            ['message' => 'Menus retrieved successfully', 'level' => 'info', 'data' => [], 'date' => '2026-08-21 10:00:00'],
        ];
    }

    public function test_a_successful_job_is_persisted(): void
    {
        JobSuccessfullyProcessed::dispatch(
            'uuid-success',
            'database',
            'default',
            self::PAYLOAD,
            $this->logs(),
            '2026-08-21 09:59:00',
            '2026-08-21 10:00:00'
        );

        $this->assertDatabaseHas('successful_jobs', [
            'uuid' => 'uuid-success',
            'connection' => 'database',
            'queue' => 'default',
        ]);

        $job = SuccessfulJob::where('uuid', 'uuid-success')->first();
        $this->assertSame('App\\Jobs\\UpdateMenusFromApiJob', $job->payload['displayName']);
        $this->assertSame('Menus retrieved successfully', $job->result[0]['message']);
        $this->assertSame('2026-08-21 10:00:00', $job->finished_at->format('Y-m-d H:i:s'));
    }

    public function test_a_failed_job_is_persisted_with_its_logs_and_exception(): void
    {
        JobFailed::dispatch(
            'uuid-failed',
            'database',
            'default',
            self::PAYLOAD,
            $this->logs(),
            "RuntimeException: boom\n#0 stack trace",
            '2026-08-21 09:59:00',
            '2026-08-21 10:00:00'
        );

        $job = FailedJob::where('uuid', 'uuid-failed')->first();

        $this->assertNotNull($job);
        $this->assertStringContainsString('RuntimeException: boom', $job->exception);
        $this->assertSame('Menus retrieved successfully', $job->logs[0]['message']);
        $this->assertSame('2026-08-21 10:00:00', $job->failed_at->format('Y-m-d H:i:s'));
    }

    public function test_a_successful_job_notifies_slack_and_discord(): void
    {
        Http::fake();
        config([
            'services.slack.notifications_enabled' => true,
            'services.slack.webhook_url_success' => 'https://hooks.slack.test/success',
            'services.discord.notifications_enabled' => true,
            'services.discord.webhook_url_success' => 'https://discord.test/success',
        ]);

        JobSuccessfullyProcessed::dispatch(
            'uuid-notify',
            'database',
            'default',
            self::PAYLOAD,
            [],
            '2026-08-21 09:59:00',
            '2026-08-21 10:00:00'
        );

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->url() === 'https://hooks.slack.test/success');
        Http::assertSent(fn ($request) => $request->url() === 'https://discord.test/success');
    }

    public function test_a_failed_job_notifies_slack_and_discord(): void
    {
        Http::fake();
        config([
            'services.slack.notifications_enabled' => true,
            'services.slack.webhook_url_failed' => 'https://hooks.slack.test/failed',
            'services.discord.notifications_enabled' => true,
            'services.discord.webhook_url_failed' => 'https://discord.test/failed',
        ]);

        JobFailed::dispatch(
            'uuid-notify-failed',
            'database',
            'default',
            self::PAYLOAD,
            [],
            'RuntimeException: boom',
            '2026-08-21 09:59:00',
            '2026-08-21 10:00:00'
        );

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->url() === 'https://hooks.slack.test/failed');
        Http::assertSent(fn ($request) => $request->url() === 'https://discord.test/failed');
    }

    public function test_a_duplicate_uuid_does_not_bubble_up(): void
    {
        $dispatch = fn () => JobSuccessfullyProcessed::dispatch(
            'uuid-duplicate',
            'database',
            'default',
            self::PAYLOAD,
            [],
            '2026-08-21 09:59:00',
            '2026-08-21 10:00:00'
        );

        $dispatch();
        $dispatch();

        $this->assertSame(1, SuccessfulJob::where('uuid', 'uuid-duplicate')->count());
    }
}
