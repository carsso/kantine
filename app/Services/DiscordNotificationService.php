<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Vite;

class DiscordNotificationService
{
    public function notifyJobFailed(
        string $uuid,
        array $payload,
        string $exception,
        string $failed_at
    ): void {
        if (! $this->isEnabled() || empty(config('services.discord.webhook_url_failed'))) {
            return;
        }

        Log::info('Sending Discord notification for failed job '.$uuid);
        try {
            $displayName = explode('\\', $payload['displayName'])[count(explode('\\', $payload['displayName'])) - 1];
            $tenantId = 0;
            $command = $payload['data']['command'] ?? null;
            if ($command) {
                $deserialized = unserialize($command);
                $tenantId = $deserialized->tenant->id ?? 0;
            }
            $tenant = Tenant::find($tenantId);
            $tenantName = $tenant ? $tenant->name : 'Inconnu';
            $firstExceptionLine = explode("\n", $exception)[0];
            $message = [
                'username' => config('app.name'),
                'avatar_url' => Vite::asset('resources/images/favicon.png'),
                'content' => "❌ **Job Failed : {$displayName} - {$tenantName} (ID: {$tenantId})**\n".
                    route('admin.jobs')."\n".
                    "**UUID:** `{$uuid}`\n".
                    "**Failed at:** `{$failed_at}`\n".
                    "**Exception:** ```{$firstExceptionLine}```",
            ];

            Http::post(config('services.discord.webhook_url_failed'), $message);
        } catch (\Exception $e) {
            Log::error('Failed to send Discord notification for failed job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function notifyJobSuccess(
        string $uuid,
        array $payload,
        string $finished_at
    ): void {
        if (! $this->isEnabled() || empty(config('services.discord.webhook_url_success'))) {
            return;
        }

        Log::info('Sending Discord notification for successful job '.$uuid);
        try {
            $displayName = explode('\\', $payload['displayName'])[count(explode('\\', $payload['displayName'])) - 1];
            $tenantId = 0;
            $command = $payload['data']['command'] ?? null;
            if ($command) {
                $deserialized = unserialize($command);
                $tenantId = $deserialized->tenant->id ?? 0;
            }
            $tenant = Tenant::find($tenantId);
            $tenantName = $tenant ? $tenant->name : 'Inconnu';
            $message = [
                'username' => config('app.name'),
                'avatar_url' => Vite::asset('resources/images/favicon.png'),
                'content' => "✅ **Job Completed : {$displayName} - {$tenantName} (ID: {$tenantId})**\n".
                    route('admin.jobs')."\n".
                    "**UUID:** `{$uuid}`\n".
                    "**Finished at:** `{$finished_at}`",
            ];

            Http::post(config('services.discord.webhook_url_success'), $message);
        } catch (\Exception $e) {
            Log::error('Failed to send Discord notification for successful job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function isEnabled(): bool
    {
        return config('services.discord.notifications_enabled', false);
    }
}
