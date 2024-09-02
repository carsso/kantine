<?php

namespace App\Libraries;

use App\Libraries\WebexApiClient;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

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

    public function put($path, $data = [], $headers = [])
    {
        $json = $this->client->getJson($this->client->put($path, json_encode($data), $headers));
        return json_decode($json, true);
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
}
