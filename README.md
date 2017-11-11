# middlewares/https

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-scrutinizer]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]
[![SensioLabs Insight][ico-sensiolabs]][link-sensiolabs]

Middleware to redirect to `https` if the request is `http` and add the [Strict Transport Security](https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security) header to protect against protocol downgrade attacks and cookie hijacking.

## Requirements

* PHP >= 7.0
* A [PSR-7](https://packagist.org/providers/psr/http-message-implementation) http mesage implementation ([Diactoros](https://github.com/zendframework/zend-diactoros), [Guzzle](https://github.com/guzzle/psr7), [Slim](https://github.com/slimphp/Slim), etc...)
* A [PSR-15 middleware dispatcher](https://github.com/middlewares/awesome-psr15-middlewares#dispatcher)

## Installation

This package is installable and autoloadable via Composer as [middlewares/https](https://packagist.org/packages/middlewares/https).

```sh
composer require middlewares/https
```

## Example

```php
$dispatcher = new Dispatcher([
	(new Middlewares\Https())
		->includeSubdomains()
]);

$response = $dispatcher->dispatch(new ServerRequest());
```

## Options

#### `maxAge(int $maxAge)`

`max-age` directive for the `Strict-Transport-Security` header. By default is `31536000` (1 year).

#### `includeSubdomains(bool $includeSubdomains = true)`

Set `true` to add the `includeSubDomains` directive to the `Strict-Transport-Security` header (`false` by default)

#### `preload(bool $preload = true)`

Set `true` to add the `preload` directive to the `Strict-Transport-Security` header (`false` by default)

#### `checkHttpsForward(bool $checkHttpsForward = true)`

If it's `true` and the request contains the headers `X-Forwarded-Proto: https` or `X-Forwarded-Port: 443`, no redirection is returned. This prevent problems with Https load balancer.

---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes and [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/middlewares/https.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/middlewares/https/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/g/middlewares/https.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/middlewares/https.svg?style=flat-square
[ico-sensiolabs]: https://img.shields.io/sensiolabs/i/763e4b16-798b-4c40-ae8a-da1698caae62.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/middlewares/https
[link-travis]: https://travis-ci.org/middlewares/https
[link-scrutinizer]: https://scrutinizer-ci.com/g/middlewares/https
[link-downloads]: https://packagist.org/packages/middlewares/https
[link-sensiolabs]: https://insight.sensiolabs.com/projects/763e4b16-798b-4c40-ae8a-da1698caae62
