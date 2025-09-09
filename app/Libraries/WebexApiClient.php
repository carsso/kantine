<?php
declare(strict_types=1);

namespace App\Libraries;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use App\Exceptions\WebexRateLimitException;
use Illuminate\Support\Facades\Log;

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
        
        try {
            return $this->guzzleClient->get(self::API_BASE_URL . $url, $options);
        } catch (ClientException $e) {
            $this->handleRateLimitException($e);
            throw $e;
        }
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
        
        try {
            return $this->guzzleClient->post(self::API_BASE_URL . $url, $options);
        } catch (ClientException $e) {
            $this->handleRateLimitException($e);
            throw $e;
        }
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
        
        try {
            return $this->guzzleClient->put(self::API_BASE_URL . $url, $options);
        } catch (ClientException $e) {
            $this->handleRateLimitException($e);
            throw $e;
        }
    }

    public function delete(string $url, ?array $customHeaders = []): ResponseInterface
    {
        $headers = array_merge(
            $this->getAuthorizationHeader(),
            $customHeaders
        );
        
        try {
            return $this->guzzleClient->delete(self::API_BASE_URL . $url, ['headers' => $headers]);
        } catch (ClientException $e) {
            $this->handleRateLimitException($e);
            throw $e;
        }
    }

    public function getJson(ResponseInterface $response): string
    {
        return $response->getBody()->getContents();
    }

    private function getAuthorizationHeader(): array
    {
        return ['Authorization' => sprintf('Bearer %s', $this->bearerToken)];
    }

    private function handleRateLimitException(ClientException $e): void
    {
        if ($e->getCode() === 429) {
            $response = $e->getResponse();
            $retryAfter = null;
            
            if ($response && $response->hasHeader('Retry-After')) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
            }
            
            Log::warning('Webex API rate limit exceeded', [
                'retry_after' => $retryAfter,
                'status_code' => 429,
                'response_body' => $response ? $response->getBody()->getContents() : null,
                'exception' => $e->getMessage()
            ]);
            
            throw new WebexRateLimitException(
                'Webex API rate limit exceeded',
                $retryAfter,
                429,
                $e
            );
        }
    }
}
