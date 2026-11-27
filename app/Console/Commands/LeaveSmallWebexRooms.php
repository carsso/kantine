<?php

namespace App\Console\Commands;

use App\Libraries\WebexApi;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Sleep;

class LeaveSmallWebexRooms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kantine:webex-small-rooms
                            {tenant_slug? : Only check this tenant}
                            {--min-members=10 : Rooms with fewer people than this are listed}
                            {--room= : Only check this room id}
                            {--leave : Make the bot leave the listed rooms (dry run otherwise)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List the Webex rooms with fewer than N people, and make the bot leave them with --leave';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $minMembers = (int) $this->option('min-members');
        $leave = (bool) $this->option('leave');

        $tenants = Tenant::where('is_active', true)
            ->whereNotNull('webex_bearer_token')
            ->when($this->argument('tenant_slug'), fn ($query, $slug) => $query->where('slug', $slug))
            ->get();

        if ($tenants->isEmpty()) {
            $this->error('No active tenant with a Webex bot found');

            return self::FAILURE;
        }

        foreach ($tenants as $tenant) {
            $this->info('Webex rooms of tenant '.$tenant->name.' with fewer than '.$minMembers.' people');

            $api = app(WebexApi::class, ['tenant' => $tenant]);
            $smallRooms = $this->findSmallRooms($api, $tenant, $minMembers);

            if (empty($smallRooms)) {
                $this->info('None found');

                continue;
            }

            $this->table(
                ['Room', 'People', 'Id'],
                array_map(fn (array $room) => [$room['title'], $room['people'], $room['id']], $smallRooms),
            );

            if (! $leave) {
                $this->warn('Dry run: run again with --leave to make the bot leave these '.count($smallRooms).' rooms');

                continue;
            }

            foreach ($smallRooms as $room) {
                $this->leaveRoom($api, $tenant, $room);
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{id: string, title: string, people: int, isReadOnly: bool, botMembershipId: ?string}>
     */
    private function findSmallRooms(WebexApi $api, Tenant $tenant, int $minMembers): array
    {
        $smallRooms = [];

        $rooms = $this->option('room')
            ? [$api->getRoom($this->option('room'))]
            : $api->getRooms('group', 1000)['items'] ?? [];

        foreach ($rooms as $room) {
            $memberships = $api->getRoomMemberships($room['id'])['items'] ?? [];
            // Bots, ours included, don't count as people.
            $people = collect($memberships)->reject(fn (array $membership) => str_ends_with($membership['personEmail'] ?? '', '@webex.bot'))->count();

            if ($people < $minMembers) {
                $smallRooms[] = [
                    'id' => $room['id'],
                    'title' => $room['title'] ?? '',
                    'people' => $people,
                    'isReadOnly' => (bool) ($room['isReadOnly'] ?? false),
                    'botMembershipId' => collect($memberships)->firstWhere('personEmail', $tenant->webex_bot_name)['id'] ?? null,
                ];
            }

            Sleep::for(200)->milliseconds();
        }

        return $smallRooms;
    }

    /**
     * @param  array{id: string, title: string, people: int, isReadOnly: bool, botMembershipId: ?string}  $room
     */
    private function leaveRoom(WebexApi $api, Tenant $tenant, array $room): void
    {
        if (! $room['botMembershipId']) {
            $this->error('Bot membership not found in room "'.$room['title'].'", skipping');

            return;
        }

        if (! $room['isReadOnly']) {
            try {
                $api->postMessage($room['id'], $this->farewellMessage($tenant));
            } catch (\Exception $e) {
                $this->warn('Could not post the farewell message in "'.$room['title'].'": '.$e->getMessage());
            }
        }

        try {
            $api->deleteMembership($room['botMembershipId']);
            $this->info('Left room "'.$room['title'].'"');
        } catch (\Exception $e) {
            $this->error('Could not leave room "'.$room['title'].'": '.$e->getMessage());
        }

        Sleep::for(1)->second();
    }

    private function farewellMessage(Tenant $tenant): string
    {
        return 'Je quitte cet espace : le menu n\'est plus envoyé qu\'aux espaces d\'au moins '.$this->option('min-members').' personnes. '
            .'Pour continuer à le recevoir, rejoignez l\'espace « Kantine » dans l\'équipe Webex de votre Valley. '
            .'Le menu reste aussi sur <a href="'.route('menus', ['tenantSlug' => $tenant->slug]).'">'.config('app.name').'</a>.';
    }
}
