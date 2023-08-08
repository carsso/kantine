<?php
declare(strict_types=1);

namespace App\Libraries;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class WebexApiClient
{
    const API_BASE_URL = 'https://webexapis.com/';

    /**
     * @var ClientInterface
     */
    public $guzzleClient;
    /**
     * @var string
     */
    private $bearerToken;

    public function __construct(ClientInterface $guzzleClient, string $bearerToken)
    {
        $this->guzzleClient = $guzzleClient;
        $this->bearerToken = $bearerToken;
    }

    public function get(string $url, ?array $queryParameters = null, ?array $customHeaders = []): ResponseInterface
    {
        $headers = array_merge(
            $this->getAuthorizationHeader(),
            $customHeaders
        );
        $options = array_merge(['headers' => $headers], ['query' => $queryParameters]);
        return $this->guzzleClient->get(self::API_BASE_URL . $url, $options);
    }

    public function post(string $url, string $jsonData, ?array $customHeaders = []): ResponseInterface
    {
        $headers = array_merge(
            $this->getAuthorizationHeader(),
            [
                'content-type' => 'application/json',
                'Accept' => 'application/json',
            ],
            $customHeaders
        );
        $options = array_merge(['headers' => $headers], ['body' => $jsonData]);
        return $this->guzzleClient->post(self::API_BASE_URL . $url, $options);
    }

    public function put(string $url, string $jsonData, ?array $customHeaders = []): ResponseInterface
    {
        $headers = array_merge(
            $this->getAuthorizationHeader(),
            [
                'content-type' => 'application/json',
                'Accept' => 'application/json',
            ],
            $customHeaders
        );
        $options = array_merge(['headers' => $headers], ['body' => $jsonData]);
        return $this->guzzleClient->put(self::API_BASE_URL . $url, $options);
    }

    public function delete(string $url, ?array $customHeaders = []): ResponseInterface
    {
        $headers = array_merge(
            $this->getAuthorizationHeader(),
            $customHeaders
        );
        return $this->guzzleClient->delete(self::API_BASE_URL . $url, ['headers' => $headers]);
    }

    public function getJson(ResponseInterface $response): string
    {
        return $response->getBody()->getContents();
    }

    private function getAuthorizationHeader(): array
    {
        return ['Authorization' => sprintf('Bearer %s', $this->bearerToken)];
    }
}
