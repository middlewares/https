# middlewares/https

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
![Testing][ico-ga]
[![Total Downloads][ico-downloads]][link-downloads]

Middleware to redirect to `https` if the request is `http` and add the [Strict Transport Security](https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security) header to protect against protocol downgrade attacks and cookie hijacking.

## Requirements

* PHP >= 7.2
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

## Usage

This middleware accept a `Psr\Http\Message\ResponseFactoryInterface` as a constructor argument, to create the redirect responses. If it's not defined, [Middleware\Utils\Factory](https://github.com/middlewares/utils#factory) will be used to detect it automatically.

```php
$responseFactory = new MyOwnResponseFactory();

//Detect the response factory automatically
$https = new Middlewares\Https();

//Use a specific factory
$htts = new Middlewares\Https($responseFactory);
```

### maxAge

This option allow to define the value of `max-age` directive for the `Strict-Transport-Security` header. By default is `31536000` (1 year).

```php
$threeYears = 31536000 * 3;

$https = (new Middlewares\Https())->maxAge($threeYears);
```

### includeSubdomains

By default, the `includeSubDomains` directive is not included in the `Strict-Transport-Security` header. Use this function to change this behavior.

```php
$https = (new Middlewares\Https())->includeSubdomains();
```

### preload

By default, the `preload` directive is not included in the `Strict-Transport-Security` header. Use this function to change this behavior.

```php
$https = (new Middlewares\Https())->preload();
```

### checkHttpsForward

Enabling this option ignore requests containing the header `X-Forwarded-Proto: https` or `X-Forwarded-Port: 443`. This is specially useful if the site is behind a https load balancer.

```php
$https = (new Middlewares\Https())->checkHttpsForward();
```

### redirect

This option returns a redirection response from `http` to `https`. It's enabled by default.

```php
//Disable redirections
$https = (new Middlewares\Https())->redirect(false);
```

---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes and [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/middlewares/https.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-ga]: https://github.com/middlewares/https/workflows/testing/badge.svg
[ico-downloads]: https://img.shields.io/packagist/dt/middlewares/https.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/middlewares/https
[link-downloads]: https://packagist.org/packages/middlewares/https
