<?php

namespace App\Libraries;

use App\Libraries\WebexApiClient;
use GuzzleHttp\Client as GuzzleClient;

class WebexApi
{
    public $client;

    public function __construct()
    {
        $this->client = new WebexApiClient(
            new GuzzleClient(),
            config('services.webex.bearer_token'),
        );
    }

    public function get($path, $headers = [])
    {
        $json = $this->client->getJson($this->client->get($path, null, $headers));
        return json_decode($json, true);
    }

    public function post($path, $data = [], $headers = [])
    {
        $json = $this->client->getJson($this->client->post($path, json_encode($data), $headers));
        return json_decode($json, true);
    }

    public function getRooms()
    {
        return $this->get('v1/rooms');
    }

    public function postMessage($roomId, $message)
    {
        return $this->post('v1/messages', [
            'roomId' => $roomId,
            'html' => $message,
        ]);
    }
}
