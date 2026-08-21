<?php

namespace Tests\Feature;

use App\Events\MenuUpdatedEvent;
use App\Jobs\UpdateMenusFromApiJob;
use App\Models\DishCategory;
use App\Models\Tenant;
use App\Services\WebexNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Tests\Concerns\BuildsMenus;
use Tests\TestCase;

class UpdateMenusFromApiJobTest extends TestCase
{
    use BuildsMenus;

    private const API_URL = 'https://api.example.test/menus';

    private Tenant $tenant;

    private DishCategory $plats;

    /**
     * The client filters the API rows with date('Ymd'), which ignores
     * Carbon::setTestNow(), so the fixtures have to use the real current day.
     */
    private string $today;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([MenuUpdatedEvent::class]);
        $this->today = date('Y-m-d');
        Carbon::setTestNow(Carbon::parse($this->today.' 10:00:00'));

        $this->tenant = Tenant::factory()->create([
            'name' => 'Lille',
            'meta' => [
                'api_type' => 'api-restauration',
                'api_url' => self::API_URL,
                'api_category_mapping' => ['Grill' => 'pole-chaud'],
            ],
        ]);

        $this->createCategoryTree($this->tenant, 'mains', 'Pole Chaud', 'Plats');
        $this->plats = DishCategory::where('tenant_id', $this->tenant->id)->where('name_slug', 'plats')->first();
        DishCategory::factory()
            ->childOf(DishCategory::where('name_slug', 'pole-chaud')->first())
            ->create(['name' => 'Garnitures', 'type' => 'mains']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * @return array<string, string>
     */
    private function apiItem(array $overrides = []): array
    {
        return array_merge([
            'periode' => 'midi',
            'rupture' => 'FALSE',
            'feuille' => 'Grill',
            'date' => date('d/m/Y'),
            'dateUs' => date('Ymd'),
            'nom' => 'Poulet rôti',
            'info1' => '',
            'info2' => '',
            'accompagnement' => 'FALSE',
            'vegetarien' => 'FALSE',
            'bio' => 'FALSE',
            'local' => 'FALSE',
            'saison' => 'FALSE',
            'equitable' => 'FALSE',
            'peche' => 'FALSE',
            'france' => 'FALSE',
        ], $overrides);
    }

    private function silentWebexService(): WebexNotificationService
    {
        return $this->mock(WebexNotificationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendMenuNotifications')->andReturn([
                'success' => true,
                'message' => 'ok',
                'notifications_sent' => 1,
            ]);
        });
    }

    public function test_it_refuses_a_tenant_without_an_api_type(): void
    {
        $tenant = Tenant::factory()->create(['meta' => []]);

        $this->expectExceptionMessage('API Type is not set');
        (new UpdateMenusFromApiJob($tenant))->handleLoggedJob($this->silentWebexService());
    }

    public function test_it_refuses_an_unsupported_api_type(): void
    {
        $tenant = Tenant::factory()->create(['meta' => ['api_type' => 'autre-api']]);

        $this->expectExceptionMessage('Unsupported API type: autre-api');
        (new UpdateMenusFromApiJob($tenant))->handleLoggedJob($this->silentWebexService());
    }

    public function test_it_imports_the_menus_returned_by_the_api(): void
    {
        Http::fake([self::API_URL => Http::response([$this->apiItem()])]);

        $job = new UpdateMenusFromApiJob($this->tenant);
        $job->handleLoggedJob($this->silentWebexService());

        $this->assertDatabaseHas('dishes', [
            'tenant_id' => $this->tenant->id,
            'date' => $this->today,
            'name' => 'Poulet rôti',
        ]);
        $this->assertFalse($job->doNotLog);
    }

    public function test_it_records_its_progress_in_the_job_logs(): void
    {
        Http::fake([self::API_URL => Http::response([$this->apiItem()])]);

        $job = new UpdateMenusFromApiJob($this->tenant);
        $job->handleLoggedJob($this->silentWebexService());

        $messages = array_column($job->jobLogs, 'message');
        $this->assertContains('Menus retrieved successfully', $messages);
        $this->assertContains('Menus updated successfully', $messages);
    }

    public function test_it_stays_silent_when_nothing_changed(): void
    {
        Http::fake([self::API_URL => Http::response([$this->apiItem()])]);
        $this->createDish($this->plats, $this->today, 'Poulet rôti');

        $job = new UpdateMenusFromApiJob($this->tenant);
        $job->handleLoggedJob($this->silentWebexService());

        $this->assertTrue($job->doNotLog);
        $this->assertContains('No changes to menus', array_column($job->jobLogs, 'message'));
    }

    public function test_it_stays_silent_when_the_api_returns_nothing(): void
    {
        Http::fake([self::API_URL => Http::response([])]);

        $job = new UpdateMenusFromApiJob($this->tenant);
        $job->handleLoggedJob($this->silentWebexService());

        $this->assertTrue($job->doNotLog);
    }

    public function test_it_notifies_webex_when_today_changed_inside_the_time_window(): void
    {
        Http::fake([self::API_URL => Http::response([$this->apiItem()])]);

        $webexService = $this->mock(WebexNotificationService::class);
        $webexService->shouldReceive('sendMenuNotifications')
            ->once()
            ->withArgs(fn (Tenant $tenant, string $date, bool $notifyUpdate, string $initiator) => $tenant->is($this->tenant)
                    && $date === $this->today
                    && $notifyUpdate === true
                    && $initiator === 'API Restauration (API)')
            ->andReturn(['success' => true, 'message' => 'ok', 'notifications_sent' => 1]);

        (new UpdateMenusFromApiJob($this->tenant))->handleLoggedJob($webexService);
    }

    public function test_it_does_not_notify_webex_before_the_time_window(): void
    {
        Carbon::setTestNow(Carbon::parse($this->today.' 09:15:00'));
        Http::fake([self::API_URL => Http::response([$this->apiItem()])]);

        $webexService = $this->mock(WebexNotificationService::class);
        $webexService->shouldNotReceive('sendMenuNotifications');

        (new UpdateMenusFromApiJob($this->tenant))->handleLoggedJob($webexService);

        $this->assertDatabaseHas('dishes', ['name' => 'Poulet rôti']);
    }

    public function test_it_does_not_notify_webex_after_the_time_window(): void
    {
        Carbon::setTestNow(Carbon::parse($this->today.' 16:00:00'));
        Http::fake([self::API_URL => Http::response([$this->apiItem()])]);

        $webexService = $this->mock(WebexNotificationService::class);
        $webexService->shouldNotReceive('sendMenuNotifications');

        (new UpdateMenusFromApiJob($this->tenant))->handleLoggedJob($webexService);
    }

    public function test_it_does_not_notify_webex_when_only_a_future_day_changed(): void
    {
        Http::fake([self::API_URL => Http::response([
            $this->apiItem([
                'date' => now()->addDays(3)->format('d/m/Y'),
                'dateUs' => now()->addDays(3)->format('Ymd'),
            ]),
        ])]);

        $webexService = $this->mock(WebexNotificationService::class);
        $webexService->shouldNotReceive('sendMenuNotifications');

        (new UpdateMenusFromApiJob($this->tenant))->handleLoggedJob($webexService);

        $this->assertDatabaseHas('dishes', [
            'date' => now()->addDays(3)->format('Y-m-d'),
            'name' => 'Poulet rôti',
        ]);
    }

    public function test_it_propagates_an_api_failure(): void
    {
        Http::fake([self::API_URL => Http::response('boom', 500)]);

        $this->expectExceptionMessage('Erreur lors de la récupération du menu');
        (new UpdateMenusFromApiJob($this->tenant))->handleLoggedJob($this->silentWebexService());
    }

    public function test_the_job_is_unique_per_tenant(): void
    {
        $this->assertSame((string) $this->tenant->id, (string) (new UpdateMenusFromApiJob($this->tenant))->uniqueId());
    }
}
