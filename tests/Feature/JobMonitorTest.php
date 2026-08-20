<?php

namespace Tests\Feature;

use App\Models\FailedJob;
use App\Models\SuccessfulJob;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantRolesAndPermissionsService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class JobMonitorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app(TenantRolesAndPermissionsService::class)->createTenantRolesAndPermissions();
    }

    private function actingAsSuperAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function jobRow(array $overrides = []): array
    {
        return array_merge([
            'uuid' => fake()->uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\UpdateMenusFromApiJob']),
        ], $overrides);
    }

    public function test_a_user_without_the_admin_permission_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.jobs'))->assertForbidden();
        $this->get('/admin/api/jobs')->assertForbidden();
    }

    public function test_the_jobs_page_renders_for_an_admin(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('admin.jobs'))->assertOk();
    }

    public function test_it_returns_pending_failed_and_successful_jobs(): void
    {
        $this->actingAsSuperAdmin();

        // The queue driver writes into `jobs` directly; the table has no
        // updated_at column, so the model cannot insert rows itself.
        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\UpdateMenusFromApiJob']),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ]);
        FailedJob::create($this->jobRow([
            'exception' => 'RuntimeException: boom',
            'failed_at' => now()->subHour(),
        ]));
        SuccessfulJob::create($this->jobRow([
            'result' => json_encode([]),
            'finished_at' => now()->subMinutes(30),
        ]));

        $response = $this->getJson('/admin/api/jobs');

        $response->assertOk();
        $response->assertJsonCount(1, 'pendingJobs');
        $response->assertJsonCount(1, 'failedJobs');
        $response->assertJsonCount(1, 'successfulJobs');
        $response->assertJsonPath('stats.pending', 1);
        $response->assertJsonPath('stats.failed', 1);
        $response->assertJsonPath('stats.successful', 1);
    }

    public function test_the_daily_and_weekly_stats_only_count_the_relevant_rows(): void
    {
        $this->actingAsSuperAdmin();

        FailedJob::create($this->jobRow(['exception' => 'boom', 'failed_at' => now()]));
        FailedJob::create($this->jobRow(['exception' => 'boom', 'failed_at' => now()->subDays(3)]));
        FailedJob::create($this->jobRow(['exception' => 'boom', 'failed_at' => now()->subDays(30)]));

        $response = $this->getJson('/admin/api/jobs');

        $response->assertJsonPath('stats.failed', 3);
        $response->assertJsonPath('stats.failed_today', 1);
        $response->assertJsonPath('stats.failed_week', 2);
    }

    public function test_it_keeps_at_most_twenty_finished_jobs(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 15; $i++) {
            FailedJob::create($this->jobRow([
                'exception' => 'boom',
                'failed_at' => now()->subMinutes($i),
            ]));
            SuccessfulJob::create($this->jobRow([
                'result' => json_encode([]),
                'finished_at' => now()->subMinutes($i),
            ]));
        }

        $response = $this->getJson('/admin/api/jobs');

        $payload = $response->json();
        $this->assertLessThanOrEqual(20, count($payload['failedJobs']) + count($payload['successfulJobs']));
    }

    public function test_it_exposes_the_tenants_keyed_by_id(): void
    {
        $this->actingAsSuperAdmin();
        $tenant = Tenant::factory()->create(['name' => 'Lille']);

        $response = $this->getJson('/admin/api/jobs');

        $response->assertJsonPath('tenants.'.$tenant->id.'.name', 'Lille');
    }

    public function test_the_payload_of_a_failed_job_is_decoded(): void
    {
        $this->actingAsSuperAdmin();
        FailedJob::create($this->jobRow([
            'exception' => 'boom',
            'failed_at' => now(),
        ]));

        $response = $this->getJson('/admin/api/jobs');

        $response->assertJsonPath('failedJobs.0.payload.displayName', 'App\\Jobs\\UpdateMenusFromApiJob');
    }
}
