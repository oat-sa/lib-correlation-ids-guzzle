# Correlation Ids Guzzle Middleware Library

> PHP library for correlation ids guzzle middleware based on the [correlation ids library](https://github.com/oat-sa/lib-correlation-ids).

## Table of contents
- [Installation](#installation)
- [Principles](#principles)
- [Usage](#usage)
- [Tests](#tests)

## Installation

```console
$ composer require oat-sa/lib-correlation-ids-guzzle
```

## Principles

This library provides a ready to use [guzzle](http://docs.guzzlephp.org/en/stable/) middleware that forwards, as request headers, the correlation ids fetched from the [correlation ids registry](https://github.com/oat-sa/lib-correlation-ids/blob/master/src/Registry/CorrelationIdsRegistryInterface.php).

**Notes**
- the current process correlation id will be forwarded as the parent one,
- the root correlation id will be also forwarded.

More details about calls chaining available on the [correlation ids library](https://github.com/oat-sa/lib-correlation-ids) documentation.

## Usage

### With the provided factory

The `GuzzleClientFactory` creates for you a guzzle client with the middleware already enabled:

```php
<?php declare(strict_types=1);

use OAT\Library\CorrelationIds\Registry\CorrelationIdsRegistry;
use OAT\Library\CorrelationIds\Registry\CorrelationIdsRegistryInterface;
use OAT\Library\CorrelationIdsGuzzle\Factory\GuzzleClientFactory;
use OAT\Library\CorrelationIdsGuzzle\Middleware\CorrelationIdsGuzzleMiddleware;

/** @var CorrelationIdsRegistryInterface $registry */
$registry = new CorrelationIdsRegistry(...);

$clientFactory = new GuzzleClientFactory(new CorrelationIdsGuzzleMiddleware($registry)));

$client = $clientFactory->create(['some' => 'options']);

...

$client->request('GET', 'http://example.com'); // Will forward correlation ids as request headers automatically.
```

### Manually

You need to push the `CorrelationIdsGuzzleMiddleware` to your handler stack as follow:

```php
<?php declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use OAT\Library\CorrelationIds\Registry\CorrelationIdsRegistry;
use OAT\Library\CorrelationIds\Registry\CorrelationIdsRegistryInterface;
use OAT\Library\CorrelationIdsGuzzle\Middleware\CorrelationIdsGuzzleMiddleware;

/** @var CorrelationIdsRegistryInterface $registry */
$registry = new CorrelationIdsRegistry(...);

$handlerStack = HandlerStack::create();
$handlerStack->push(Middleware::mapRequest(new CorrelationIdsGuzzleMiddleware($registry)));

$client = new Client(['handler' => $handlerStack]);

...

$client->request('GET', 'http://example.com'); // Will forward correlation ids as request headers automatically.
```
**Note**: you can customize the log context key names by providing you own [CorrelationIdsHeaderNamesProviderInterface](https://github.com/oat-sa/lib-correlation-ids/blob/master/src/Provider/CorrelationIdsHeaderNamesProviderInterface.php) implementation and pass it to the `CorrelationIdsGuzzleMiddleware` constructor.

## Tests

To run tests:
```console
$ vendor/bin/phpunit
```
**Note**: see [phpunit.xml.dist](phpunit.xml.dist) for available test suites.