<?php

namespace Pbmengine\Restclient;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Str;
use Pbmengine\Restclient\Exceptions\HttpClientRequestException;

/**
 * Class HttpClient
 * @package Pbmengine\Restclient
 */
class HttpClient
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $body = [];

    /**
     * @var array
     */
    protected $queryParams = [];

    /**
     * HttpClient constructor.
     * the options and baseUrl are still mutable
     *
     * @param ClientInterface|null $client
     * @param string $baseUrl | base uri of the api
     * @param array $options | client options
     */
    public function __construct(ClientInterface $client = null, string $baseUrl = '', array $options = [])
    {
        $this->client = $client ?: new Client;
        $this->baseUrl($baseUrl);
        $this->options($options);
    }

    /**
     * set the base url for the request
     *
     * @param string $url
     * @return HttpClient
     */
    public function baseUrl(string $url): self
    {
        $this->baseUrl = $url;

        return $this;
    }

    /**
     * set the client options
     * existing keys will be overridden
     *
     * @param array $options
     * @return HttpClient
     */
    public function options(array $options): self
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * set single option
     * existing keys will be overridden
     *
     * @param string $key
     * @param $value
     * @return HttpClient
     */
    public function option(string $key, $value): self
    {
        $this->options([$key => $value]);

        return $this;
    }

    /**
     * set headers
     * existing keys will be overridden
     *
     * @param array $headers
     * @return HttpClient
     */
    public function headers(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * set single header
     * existing keys will be overridden
     *
     * @param $key
     * @param $value
     * @return HttpClient
     */
    public function header($key, $value): self
    {
        $this->headers([$key => $value]);

        return $this;
    }

    /**
     * get the complete body with headers, options, client body
     *
     * @return array
     */
    public function getBody(): array
    {
        return $this->buildClientOptions();
    }

    /**
     * add query params
     * existing keys will be overridden
     *
     * @param array $params
     * @return HttpClient
     */
    public function queryParams(array $params): self
    {
        $this->queryParams = array_merge($this->queryParams, $params);

        return $this;
    }

    /**
     * add single query param
     * existing keys will be overridden
     *
     * @param $key
     * @param $value
     * @return HttpClient
     */
    public function queryParam($key, $value): self
    {
        $this->queryParams([$key => $value]);

        return $this;
    }

    /**
     * set single option ssl verify
     *
     * @param bool $verify
     * @return HttpClient
     */
    public function verifySsl(bool $verify): self
    {
        $this->option('verify', $verify);

        return $this;
    }

    /**
     * set the body
     * this will override existing body values
     *
     * @param array $body
     * @return $this
     */
    protected function body(array $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * set json payload
     * content-type and accept header will be automatically set
     *
     * @param array $payload
     * @return HttpClient
     */
    public function jsonPayload(array $payload): self
    {
        $this->body == null;
        $this->header('Content-Type', 'application/json');
        $this->header('Accept', 'application/json');
        $this->body(['json' => $payload]);

        return $this;
    }

    /**
     * Sets multipart payload for file uploads
     * content-type will be automatically set
     *
     * @param array $payload
     * @return $this
     * @throws \Exception
     */
    public function multipartPayload(array $payload): self
    {
        $this->body = null;
        $this->body(['multipart' => $payload]);
        $this->header('Content-Type', 'multipart/form-data');

        return $this;
    }

    /**
     * Set form params payload
     * add automatically content type
     *
     * @param array $payload
     * @return HttpClient
     * @throws \Exception
     */
    public function formParamsPayload(array $payload): self
    {
        $this->body = null;
        $this->body(['form_params' => $payload]);
        $this->header('Content-Type', 'application/x-www-form-urlencoded');

        return $this;
    }

    /**
     * seth authorization bearer token
     *
     * @param $token
     * @return HttpClient
     */
    public function authorizationBearer($token): self
    {
        $this->header('Authorization', 'Bearer ' . $token);

        return $this;
    }

    /**
     * set authorization digest with username and password
     *
     * @param $username
     * @param $password
     * @return HttpClient
     */
    public function authorizationDigest($username, $password): self
    {
        $this->option('auth', [$username, $password, 'digest']);

        return $this;
    }

    /**
     * set authorization http with username and password
     *
     * @param $username
     * @param $password
     * @return HttpClient
     */
    public function authorizationHttp($username, $password): self
    {
        $this->option('auth', [$username, $password]);

        return $this;
    }

    /**
     * get query params for request
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * get headers for request
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * build the client options for the request
     *
     * @return array
     */
    protected function buildClientOptions(): array
    {
        return array_merge(['headers' => $this->headers], $this->body, $this->options);
    }

    /**
     * test the request url with endpoint
     *
     * @param $endpoint
     * @return string
     */
    public function getRequestUrl($endpoint): string
    {
        return $this->buildRequestUrl($endpoint);
    }

    /**
     * Build request url
     * baseUrl can be empty and could be set in endpoint
     *
     * @param $endpoint
     * @return string
     */
    protected function buildRequestUrl($endpoint): string
    {
        $endpoint = (Str::startsWith($endpoint, '/')) ?
            Str::substr($endpoint, 1) :
            $endpoint;

        $prefix = (Str::endsWith($this->baseUrl, '/') || empty ($this->baseUrl)) ? '' : '/';

        return $this->baseUrl . $prefix . $endpoint . $this->buildQueryParams();
    }

    /**
     * build the query params for url
     *
     * @return string
     */
    protected function buildQueryParams(): string
    {
        return (count($this->queryParams) == 0) ? '' : '?' . http_build_query($this->queryParams);
    }

    /**
     * request itself
     *
     * @param $method
     * @param $endpoint
     * @return HttpResponse
     * @throws HttpClientRequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request($method, $endpoint)
    {
        try {
            $response = new HttpResponse(
                $this->client->request($method, $this->buildRequestUrl($endpoint), $this->buildClientOptions())
            );
        } catch (\Exception $e) {
            // clean values in queryParams and body
            $this->clearQueryAndBodySettings();

            throw new HttpClientRequestException($e);
        }

        // clean values in queryParams and body
        $this->clearQueryAndBodySettings();

        return $response;
    }

    /**
     * Get a new clean client and reset all settings
     *
     * @return HttpClient
     */
    public function reset()
    {
        return new self();
    }

    /**
     * Clean all values in body and queryParams
     */
    protected function clearQueryAndBodySettings()
    {
        $this->body = [];
        $this->queryParams = [];
    }

    /**
     * get request
     *
     * @param string $endpoint
     * @return HttpResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $endpoint)
    {
        return $this->request('GET', $endpoint);
    }

    /**
     * post request
     *
     * @param string $endpoint
     * @return HttpResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post(string $endpoint)
    {
        return $this->request('POST', $endpoint);
    }

    /**
     * put request
     *
     * @param string $endpoint
     * @return HttpResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function put(string $endpoint)
    {
        return $this->request('PUT', $endpoint);
    }

    /**
     * patch request
     *
     * @param string $endpoint
     * @return HttpResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function patch(string $endpoint)
    {
        return $this->request('PATCH', $endpoint);
    }


    /**
     * head request
     *
     * @param string $endpoint
     * @return HttpResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function head(string $endpoint)
    {
        return $this->request('HEAD', $endpoint);
    }


    /**
     * delete request
     *
     * @param string $endpoint
     * @return HttpResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete(string $endpoint)
    {
        return $this->request('DELETE', $endpoint);
    }
}
