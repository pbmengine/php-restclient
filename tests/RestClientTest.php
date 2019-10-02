<?php

namespace Pbmengine\Restclient\Tests;

use Pbmengine\Restclient\HttpClient;
use Pbmengine\Restclient\HttpResponse;
use PHPUnit\Framework\TestCase;
use Mockery;

class RestClientTest extends TestCase
{
    public function test_adding_headers()
    {
        $client = new HttpClient($http = Mockery::mock('GuzzleHttp\Client'));

        $client->header('header1', '111');
        $this->assertArrayHasKey('header1', $client->getBody()['headers']);

        $client->headers(['header2' => '222', 'header3' => '333']);
        $this->assertEquals($client->getBody()['headers'], ['header1' => '111', 'header2' => '222', 'header3' => '333']);
    }

    public function test_adding_options()
    {
        $client = new HttpClient($http = Mockery::mock('GuzzleHttp\Client'));

        $client->option('option1', '111');
        $this->assertArrayHasKey('option1', $client->getBody());

        $client->options(['option2' => '222', 'option3' => '333']);
        $this->assertEquals($client->getBody()['option1'], '111');
        $this->assertEquals($client->getBody()['option2'], '222');
        $this->assertEquals($client->getBody()['option3'], '333');
    }

    public function test_adding_query_params()
    {
        $client = new HttpClient($http = Mockery::mock('GuzzleHttp\Client'));

        $client->queryParam('option1', '111');
        $this->assertArrayHasKey('option1', $client->getQueryParams());

        $client->queryParams(['option2' => '222', 'option3' => '333']);
        $this->assertEquals($client->getQueryParams(), ['option1' => '111', 'option2' => '222', 'option3' => '333']);
    }

    public function test_set_authorization_bearer()
    {
        $client = new HttpClient($http = Mockery::mock('GuzzleHttp\Client'));
        $client->authorizationBearer('token');

        $this->assertArrayHasKey('Authorization', $client->getHeaders());
        $this->assertEquals('Bearer token', $client->getHeaders()['Authorization']);
    }

    public function test_set_verify_ssl()
    {
        $client = new HttpClient($http = Mockery::mock('GuzzleHttp\Client'));
        $client->verifySsl(true);

        $this->assertArrayHasKey('verify', $client->getBody());
        $this->assertTrue($client->getBody()['verify']);
    }

    public function test_set_json_payload()
    {
        $client = new HttpClient($http = Mockery::mock('GuzzleHttp\Client'));
        $client->formParamsPayload(['param' => 'B']);
        $client->jsonPayload(['test' => 'A']);

        $this->assertArrayHasKey('json', $client->getBody());
        $this->assertArrayHasKey('Content-Type', $client->getHeaders());
        $this->assertEquals('application/json', $client->getHeaders()['Content-Type']);
        $this->assertEquals('A', $client->getBody()['json']['test']);
        $this->assertArrayNotHasKey('form_params', $client->getBody());
    }

    public function test_set_multipart_payload()
    {
        $client = new HttpClient($http = Mockery::mock('GuzzleHttp\Client'));
        $client->formParamsPayload(['param' => 'B']);
        $client->multipartPayload(['test' => 'A']);

        $this->assertArrayHasKey('multipart', $client->getBody());
        $this->assertArrayHasKey('Content-Type', $client->getHeaders());
        $this->assertEquals('multipart/form-data', $client->getHeaders()['Content-Type']);
        $this->assertEquals('A', $client->getBody()['multipart']['test']);
        $this->assertArrayNotHasKey('form_params', $client->getBody());
    }

    public function test_set_form_params_payload()
    {
        $client = new HttpClient($http = Mockery::mock('GuzzleHttp\Client'));
        $client->multipartPayload(['test' => 'A']);
        $client->formParamsPayload(['param' => 'B']);

        $this->assertArrayHasKey('form_params', $client->getBody());
        $this->assertArrayHasKey('Content-Type', $client->getHeaders());
        $this->assertEquals('application/x-www-form-urlencoded', $client->getHeaders()['Content-Type']);
        $this->assertEquals('B', $client->getBody()['form_params']['param']);
        $this->assertArrayNotHasKey('mulitpart', $client->getBody());
    }


    public function test_request_url()
    {
        $baseUrl = 'https://test.com';
        $client = new HttpClient($http = Mockery::mock('GuzzleHttp\Client'), $baseUrl);
        $client->queryParams(['mode' => 'sync', 'test' => 12]);

        $this->assertEquals($baseUrl . '/users?mode=sync&test=12', $client->getRequestUrl('users'));
    }

    public function test_multiple_requests()
    {
        // basic stuff
        $baseUrl = 'https://test.com';
        $client = new HttpClient($http = Mockery::mock('GuzzleHttp\Client'), $baseUrl);

        // set headers
        $client->authorizationBearer('token');
        $client->option('timeout', 30);

        // test get request
        $client->queryParams(['mode' => 'sync', 'test' => 12]);

        try {
            $client->get('users');
        } catch(\Exception $e) { }

        $this->assertEquals('Bearer token', $client->getHeaders()['Authorization']);
        $this->assertArrayNotHasKey('mode', $client->getQueryParams());

        // change request and options
        // do post
        $client->jsonPayload(['a' => 'b']);

        try {
            $client->post('users');
        } catch (\Exception $e) { }

        $this->assertArrayHasKey('Authorization', $client->getHeaders());
    }

    public function test_response()
    {
        $http = Mockery::mock('GuzzleHttp\Client');

        $stream = Mockery::mock('GuzzleHttp\Psr7\Stream');
        $stream->shouldReceive('getContents')->once()->andReturn('{"name": "test"}');

        $mockResponse = Mockery::mock('GuzzleHttp\Psr7\Response');
        $mockResponse->shouldReceive('getStatusCode')->andReturn(200);
        $mockResponse->shouldReceive('getBody')->once()->andReturn($stream);

        $http->shouldReceive('request')->with('GET', 'tests', [])->andReturn(
            $response = new HttpResponse($mockResponse)
        );

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals(['name' => 'test'], $response->contentAsArray());
        $this->assertEquals(collect(['name' => 'test']), $response->contentAsCollection());
        $this->assertEquals(true, $response->isValid());

    }


}
