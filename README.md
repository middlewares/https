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
* A [PSR-7 http library](https://github.com/middlewares/awesome-psr15-middlewares#psr-7-implementations)
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

## API

### `__construct`

Type | Required | Description
-----|----------|------------
`Psr\Http\Message\ResponseFactoryInterface` | No | A PSR-17 factory to create redirect responses. If it's not defined, use [Middleware\Utils\Factory](https://github.com/middlewares/utils#factory) to detect it automatically.

### `maxAge`

Changes the value of `max-age` directive for the `Strict-Transport-Security` header. By default is `31536000` (1 year).

Type | Required | Description
-----|----------|------------
`int` | Yes | The new value in seconds

### `includeSubdomains`

By default, the `includeSubDomains` directive is not included in the `Strict-Transport-Security` header. Use this function to change this behavior.

Type | Required | Description
-----|----------|------------
`bool` | No | `true` to include the directive, `false` to don't. By default is `true`.

### `preload`

By default, the `preload` directive is not included in the `Strict-Transport-Security` header. Use this function to change this behavior.

Type | Required | Description
-----|----------|------------
`bool` | No | `true` to include the directive, `false` to don't. By default is `true`.

### `checkHttpsForward`

Enabling this option ignore requests containing the header `X-Forwarded-Proto: https` or `X-Forwarded-Port: 443`. This is specially useful if the site is behind a https load balancer.

Type | Required | Description
-----|----------|------------
`bool` | No | `true` to enable this behavior, `false` to don't. By default is `true`.

### `redirect`

This option returns a redirection response from `http` to `https`. It's enabled by default.

Type | Required | Description
-----|----------|------------
`bool` | No | `true` to enable redirections, `false` to don't. By default is `true`.

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
