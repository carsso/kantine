<?php

namespace Tests\Feature\Console;

use App\Events\DashboardRefreshEvent;
use App\Jobs\UpdateMenusFromApiJob;
use App\Models\Tenant;
use App\Services\WebexNotificationService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UpdateMenusFromApiCommandTest extends TestCase
{
    public function test_it_dispatches_a_job_for_each_tenant_wired_to_the_api(): void
    {
        Bus::fake();
        $wired = Tenant::factory()->withRestaurationApi()->create(['name' => 'Lille']);
        Tenant::factory()->create(['name' => 'Lens']);

        $this->artisan('kantine:update-menus-from-api')->assertExitCode(0);

        Bus::assertDispatchedTimes(UpdateMenusFromApiJob::class, 1);
        Bus::assertDispatched(UpdateMenusFromApiJob::class, fn ($job) => $job->tenant->is($wired));
    }

    public function test_it_skips_a_tenant_with_an_unknown_api_type(): void
    {
        Bus::fake();
        Tenant::factory()->create([
            'name' => 'Lille',
            'meta' => ['api_type' => 'autre-api', 'api_url' => 'https://example.test'],
        ]);

        $this->artisan('kantine:update-menus-from-api')
            ->expectsOutputToContain('has an unknown API Type, skipping')
            ->assertExitCode(0);

        Bus::assertNothingDispatched();
    }

    public function test_it_can_target_a_single_tenant(): void
    {
        Bus::fake();
        $lille = Tenant::factory()->withRestaurationApi()->create(['name' => 'Lille']);
        Tenant::factory()->withRestaurationApi()->create(['name' => 'Lens']);

        $this->artisan('kantine:update-menus-from-api', ['tenant_slug' => $lille->slug])->assertExitCode(0);

        Bus::assertDispatchedTimes(UpdateMenusFromApiJob::class, 1);
    }

    public function test_it_ignores_inactive_tenants_when_no_slug_is_given(): void
    {
        Bus::fake();
        Tenant::factory()->withRestaurationApi()->inactive()->create(['name' => 'Lille']);

        $this->artisan('kantine:update-menus-from-api')->assertExitCode(0);

        Bus::assertNothingDispatched();
    }

    public function test_the_refresh_dashboard_command_broadcasts_the_event(): void
    {
        Event::fake([DashboardRefreshEvent::class]);

        $this->artisan('kantine:refresh-dashboard')
            ->expectsOutputToContain('Dashboard refreshed')
            ->assertExitCode(0);

        Event::assertDispatched(DashboardRefreshEvent::class);
    }

    public function test_the_notify_webex_command_skips_a_tenant_without_a_token(): void
    {
        Tenant::factory()->create(['name' => 'Lille']);

        $this->artisan('kantine:notify-webex')
            ->expectsOutputToContain('Webex bearer token not set')
            ->assertExitCode(0);
    }

    public function test_the_notify_webex_command_targets_a_single_tenant(): void
    {
        $lille = Tenant::factory()->create(['name' => 'Lille']);
        Tenant::factory()->create(['name' => 'Lens']);

        $webexService = $this->mock(WebexNotificationService::class);
        $webexService->shouldReceive('sendMenuNotifications')
            ->once()
            ->withArgs(fn (Tenant $tenant) => $tenant->is($lille))
            ->andReturn(['success' => true, 'message' => 'ok', 'notifications_sent' => 1]);

        $this->artisan('kantine:notify-webex', ['tenant_slug' => $lille->slug])->assertExitCode(0);
    }
}
