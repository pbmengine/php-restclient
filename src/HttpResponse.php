<?php

namespace Pbmengine\Restclient;

use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpResponse
 * @package Pbmengine\Restclient
 */
class HttpResponse
{
    /**
     * @var array
     */
    protected $validStatusCodes = [200, 201, 202, 203, 204, 205, 206, 207, 208, 226];

    /**
     * @var ResponseInterface
     */
    protected $raw;

    /**
     * @var integer
     */
    protected $statusCode;

    /**
     * @var bool
     */
    protected $isValid;

    /**
     * @var string
     */
    protected $rawContent;

    /**
     * @var ResponseInterface
     */
    protected $rawResponse;

    /**
     * @var Collection
     */
    protected $content;

    /**
     * HttpResponse constructor.
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->rawResponse = $response;
        $this->rawContent = $response->getBody()->getContents();
        $this->content = $this->jsonToCollection($this->rawContent);
    }

    /**
     * is the response valid
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->statusCode() >= 200 && $this->statusCode() < 300;
    }

    /**
     * is the response a redirect
     *
     * @return bool
     */
    public function isRedirect()
    {
        return $this->statusCode() >= 300 && $this->statusCode() < 400;
    }

    /**
     * is the response a client error
     *
     * @return bool
     */
    public function isClientError()
    {
        return $this->statusCode() >= 400 && $this->statusCode() < 500;
    }

    /**
     * is the response a server error
     *
     * @return bool
     */
    public function isServerError()
    {
        return $this->statusCode() >= 500 && $this->statusCode() < 600;
    }

    /**
     * @return int
     */
    public function statusCode()
    {
        return $this->rawResponse->getStatusCode();
    }

    /**
     * get raw response
     *
     * @return ResponseInterface
     */
    public function rawResponse(): ResponseInterface
    {
        return $this->rawResponse;
    }

    /**
     * get response headers
     *
     * @return \string[][]
     */
    public function headers()
    {
        return $this->rawResponse()->getHeaders();
    }

    /**
     * get content as std class
     *
     * @return mixed
     */
    public function content()
    {
        return json_decode($this->rawContent());
    }

    /**
     * get response content as json
     *
     * @return string
     */
    public function contentAsJson(): string
    {
        return $this->contentAsCollection()->toJson();
    }

    /**
     * get response content as array
     *
     * @return array
     */
    public function contentAsArray(): array
    {
        return $this->contentAsCollection()->toArray();
    }

    /**
     * get content as illuminate collection
     *
     * @return Collection
     */
    public function contentAsCollection(): Collection
    {
        return $this->content;
    }

    /**
     * convert json to illuminate collection
     *
     * @param $json
     * @return Collection
     */
    protected function jsonToCollection($json): Collection
    {
        return collect(json_decode($json, 1));
    }
}