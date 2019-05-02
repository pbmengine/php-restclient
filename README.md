# Generic PHP client to access REST APIs

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pbmengine/php-restclient.svg?style=flat-square)](https://packagist.org/packages/pbmengine/php-restclient)
[![Build Status](https://img.shields.io/travis/pbmengine/php-restclient/master.svg?style=flat-square)](https://travis-ci.org/pbmengine/php-restclient)
[![Quality Score](https://img.shields.io/scrutinizer/g/pbmengine/php-restclient.svg?style=flat-square)](https://scrutinizer-ci.com/g/pbmengine/php-restclient)
[![Total Downloads](https://img.shields.io/packagist/dt/pbmengine/php-restclient.svg?style=flat-square)](https://packagist.org/packages/pbmengine/php-restclient)

This publication describes a generic api rest client suitable for all PHP packages.

## Installation

You can install the package via composer:

```bash
composer require pbmengine/php-restclient
```

## Basic Usage

``` php

use Pbmengine\Restclient\HttpClient;

$client = new HttpClient; 

// or

$client = new HttpClient(\GuzzleHttp\Client, 'https://example.com/v1', ['timeout' => 30]); 

$response = $client->get('users');
$response = $client->jsonPayload(['id' : 1])->post('users');

```

### Client Methods

```
$client->baseUrl('https://example.com/v1');

$client->options(['http_errors' => false, 'timeout' => 30]);
$client->option('http_errors', false);

$client->authorizationBearer('token');
$client->authorizationHttp('username', 'password');
$client->authorizationDigest('username', 'password');

$client->headers(['API-KEY' => 30]);
$client->header('API-KEY', 30);

$client->queryParams(['embed' => 'resource']);
$client->queryParam('embed', 'resource');

$client->jsonPayload([]);
$client->multipartPayload([]);
$client->formParamsPayload([]);

$client->getHeaders();
$client->getQueryParams();
$client->getBody();
$client->getRequestUrl('endpoint');

$response = $client->get('users');
$response = $client->post('users');
$response = $client->put('users/12');
$response = $client->delete('users/12');
$response = $client->patch('users/12');
$response = $client->head('users/12');

```

### Response Methods

```

$response->statusCode(); // 200
$response->headers(); // []
$response->raw(); // ResponseInterface
$response->raw()->getHeaders(); // []
$response->isValid(); // true
$response->isServerError(); // false
$response->isClientError(); // false
$response->content(); // StdClass
$response->contentAsArray(); // Array
$response->contentAsJson(); // Json String
$response->contentAsCollection(); // Illuminate Collection

```

### Use Case several requests

```

$client = (new HttpClient)
    ->baseUrl('https://example.com')
    ->authorizationBearer('your token');

// get all users    
$response = $client
    ->queryParam('embed', 'client');
    ->get('users'); 

// update user
$response = $client
    ->jsonPayload(['name' => 'John'])
    ->put('users/' . $response->content()->data->id);

// set new Bearer
$client->authorizationBearer('another token');

// delete user
$response = $client->delete('users/id');

```


### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email systems@personal-business-machine.com instead of using the issue tracker.

## Credits

- [Stefan Riehl](https://github.com/stefanriehl)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
