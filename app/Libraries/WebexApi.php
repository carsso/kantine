<?php

namespace App\Libraries;

use App\Libraries\WebexApiClient;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use App\Models\Tenant;
use App\Exceptions\WebexRateLimitException;

class WebexApi
{
    public $client;
    private $maxRetries = 3;
    private $baseDelay = 3; // seconds

    public function __construct(?Tenant $tenant = null)
    {
        if (!$tenant || !$tenant->webex_bearer_token) {
            throw new \Exception('Webex configuration not found for tenant');
        }

        $this->client = new WebexApiClient(
            new GuzzleClient(),
            $tenant->webex_bearer_token,
        );
    }

    public function get($path, $headers = [])
    {
        return $this->executeWithRetry(function() use ($path, $headers) {
            $json = $this->client->getJson($this->client->get($path, null, $headers));
            return json_decode($json, true);
        });
    }

    public function post($path, $data = [], $headers = [])
    {
        return $this->executeWithRetry(function() use ($path, $data, $headers) {
            $json = $this->client->getJson($this->client->post($path, json_encode($data), $headers));
            return json_decode($json, true);
        });
    }

    public function put($path, $data = [], $headers = [])
    {
        return $this->executeWithRetry(function() use ($path, $data, $headers) {
            $json = $this->client->getJson($this->client->put($path, json_encode($data), $headers));
            return json_decode($json, true);
        });
    }

    public function getRooms($type = 'group')
    {
        if(!empty($type)) {
            return $this->get('v1/rooms?type='.$type);
        }
        return $this->get('v1/rooms');
    }

    public function getRoom($roomId)
    {
        return $this->get('v1/rooms/' . $roomId);
    }

    public function getRoomMemberships($roomId)
    {
        return $this->get('v1/memberships?roomId=' . $roomId .'&max=1000');
    }

    public function getPerson($personId)
    {
        return $this->get('v1/people/' . $personId);
    }
    public function getOrganization($organizationId)
    {
        return $this->get('v1/organizations/' . $organizationId);
    }

    public function getMessages($roomId, $max = 50)
    {
        return $this->get('v1/messages?roomId=' . $roomId . '&mentionedPeople=me&max=' . $max);
    }

    public function postMessage($roomId, $message, $parentId = null)
    {
        return $this->post('v1/messages', [
            'roomId' => $roomId,
            'html' => $message,
            'parentId' => $parentId,
        ]);
    }

    public function updateMessage($messageId, $roomId, $message)
    {
        return $this->put('v1/messages/' . $messageId, [
            'roomId'=> $roomId,
            'html' => $message,
        ]);
    }

    /**
     * Execute a request with retry logic for rate limiting
     */
    private function executeWithRetry(callable $request, int $attempt = 1)
    {
        try {
            return $request();
        } catch (WebexRateLimitException $e) {
            if ($attempt > $this->maxRetries) {
                throw $e;
            }

            $delay = $this->calculateDelay($e->getRetryAfterSeconds(), $attempt);
            sleep($delay);

            return $this->executeWithRetry($request, $attempt + 1);
        }
    }

    /**
     * Calculate delay with exponential backoff
     */
    private function calculateDelay(int $retryAfter, int $attempt): int
    {
        // Use Retry-After header if available, otherwise use exponential backoff
        if ($retryAfter > 0) {
            return $retryAfter;
        }

        // Exponential backoff: baseDelay * 2^(attempt-1)
        return $this->baseDelay * pow(2, $attempt - 1);
    }
}
