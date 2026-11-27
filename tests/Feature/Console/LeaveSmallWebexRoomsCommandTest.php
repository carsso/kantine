<?php

namespace Tests\Feature\Console;

use App\Libraries\WebexApi;
use App\Libraries\WebexApiClient;
use App\Models\Tenant;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Sleep;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

class LeaveSmallWebexRoomsCommandTest extends TestCase
{
    /**
     * @var array<int, array{request: RequestInterface}>
     */
    private array $requests = [];

    protected function setUp(): void
    {
        parent::setUp();

        Sleep::fake();
        // The tenants migration seeds a tenant with the Webex token of the environment.
        Tenant::query()->update(['webex_bearer_token' => null]);
        Tenant::factory()->withWebex()->create(['name' => 'Roubaix']);
    }

    public function test_it_lists_small_rooms_without_leaving_them(): void
    {
        $this->fakeWebex([
            $this->webexResponse(['items' => [
                ['id' => 'small', 'title' => 'Petite équipe', 'isReadOnly' => false],
                ['id' => 'big', 'title' => 'Grande équipe', 'isReadOnly' => false],
            ]]),
            $this->webexResponse(['items' => $this->memberships(3)]),
            $this->webexResponse(['items' => $this->memberships(12)]),
        ]);

        $this->artisan('kantine:webex-small-rooms')
            ->expectsTable(['Room', 'People', 'Id'], [['Petite équipe', 3, 'small']])
            ->expectsOutputToContain('Dry run')
            ->assertExitCode(0);

        $this->assertSame(['GET', 'GET', 'GET'], $this->methods());
        $this->assertStringContainsString('max=1000', (string) $this->requests[0]['request']->getUri());
    }

    public function test_other_bots_do_not_count_as_people(): void
    {
        $memberships = $this->memberships(9);
        $memberships[] = ['id' => 'm-other-bot', 'personEmail' => 'other@webex.bot'];

        $this->fakeWebex([
            $this->webexResponse(['items' => [['id' => 'room', 'title' => 'Avec des bots', 'isReadOnly' => false]]]),
            $this->webexResponse(['items' => $memberships]),
        ]);

        $this->artisan('kantine:webex-small-rooms')
            ->expectsTable(['Room', 'People', 'Id'], [['Avec des bots', 9, 'room']])
            ->assertExitCode(0);
    }

    public function test_it_posts_a_farewell_and_leaves_with_the_leave_option(): void
    {
        $this->fakeWebex([
            $this->webexResponse(['items' => [['id' => 'small', 'title' => 'Petite équipe', 'isReadOnly' => false]]]),
            $this->webexResponse(['items' => $this->memberships(2)]),
            $this->webexResponse(['id' => 'message']),
            new Response(204),
        ]);

        $this->artisan('kantine:webex-small-rooms', ['--leave' => true])
            ->expectsOutputToContain('Left room "Petite équipe"')
            ->assertExitCode(0);

        $this->assertSame(['GET', 'GET', 'POST', 'DELETE'], $this->methods());

        $message = json_decode((string) $this->requests[2]['request']->getBody(), true);
        $this->assertSame('small', $message['roomId']);
        $this->assertStringContainsString('« Kantine »', $message['html']);
        $this->assertStringEndsWith('v1/memberships/m-bot', (string) $this->requests[3]['request']->getUri());
    }

    public function test_it_leaves_read_only_rooms_without_posting(): void
    {
        $this->fakeWebex([
            $this->webexResponse(['items' => [['id' => 'annonces', 'title' => 'Annonces', 'isReadOnly' => true]]]),
            $this->webexResponse(['items' => $this->memberships(4)]),
            new Response(204),
        ]);

        $this->artisan('kantine:webex-small-rooms', ['--leave' => true])
            ->assertExitCode(0);

        $this->assertSame(['GET', 'GET', 'DELETE'], $this->methods());
    }

    public function test_it_can_leave_a_single_room(): void
    {
        $this->fakeWebex([
            $this->webexResponse(['id' => 'test-room', 'title' => 'Salle de test', 'isReadOnly' => false]),
            $this->webexResponse(['items' => $this->memberships(1)]),
            $this->webexResponse(['id' => 'message']),
            new Response(204),
        ]);

        $this->artisan('kantine:webex-small-rooms', ['--room' => 'test-room', '--leave' => true])
            ->expectsOutputToContain('Left room "Salle de test"')
            ->assertExitCode(0);

        $this->assertSame(['GET', 'GET', 'POST', 'DELETE'], $this->methods());
        $this->assertStringEndsWith('v1/rooms/test-room', (string) $this->requests[0]['request']->getUri());
    }

    public function test_a_single_room_above_the_threshold_is_kept(): void
    {
        $this->fakeWebex([
            $this->webexResponse(['id' => 'big', 'title' => 'Grande équipe', 'isReadOnly' => false]),
            $this->webexResponse(['items' => $this->memberships(15)]),
        ]);

        $this->artisan('kantine:webex-small-rooms', ['--room' => 'big', '--leave' => true])
            ->expectsOutputToContain('None found')
            ->assertExitCode(0);

        $this->assertSame(['GET', 'GET'], $this->methods());
    }

    public function test_the_threshold_can_be_changed(): void
    {
        $this->fakeWebex([
            $this->webexResponse(['items' => [['id' => 'room', 'title' => 'Moyenne équipe', 'isReadOnly' => false]]]),
            $this->webexResponse(['items' => $this->memberships(12)]),
        ]);

        $this->artisan('kantine:webex-small-rooms', ['--min-members' => 20])
            ->expectsTable(['Room', 'People', 'Id'], [['Moyenne équipe', 12, 'room']])
            ->assertExitCode(0);
    }

    public function test_it_fails_when_no_tenant_has_a_webex_bot(): void
    {
        $this->artisan('kantine:webex-small-rooms', ['tenant_slug' => 'cantine-inconnue'])
            ->expectsOutputToContain('No active tenant with a Webex bot found')
            ->assertExitCode(1);
    }

    /**
     * @param  array<int, Response>  $responses
     */
    private function fakeWebex(array $responses): void
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->requests));

        $this->app->bind(WebexApi::class, function ($app, array $parameters) use ($stack) {
            $api = new WebexApi($parameters['tenant']);
            $api->client = new WebexApiClient(new GuzzleClient(['handler' => $stack]), 'webex-token');

            return $api;
        });
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function webexResponse(array $body): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($body));
    }

    /**
     * Memberships of the given number of people, plus the tenant's bot.
     *
     * @return array<int, array{id: string, personEmail: string}>
     */
    private function memberships(int $people): array
    {
        $memberships = [['id' => 'm-bot', 'personEmail' => 'bot@webex.bot']];
        for ($i = 1; $i <= $people; $i++) {
            $memberships[] = ['id' => 'm-'.$i, 'personEmail' => 'person'.$i.'@example.test'];
        }

        return $memberships;
    }

    /**
     * @return array<int, string>
     */
    private function methods(): array
    {
        return array_map(fn (array $entry) => $entry['request']->getMethod(), $this->requests);
    }
}
