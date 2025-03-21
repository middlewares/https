<?php
declare(strict_types = 1);

namespace Middlewares\Tests;

use Middlewares\Https;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;

class HttpsTest extends TestCase
{
    /**
     * @return array<array<string|bool|int>>
     */
    public function httpsProvider(): array
    {
        return [
            ['http://localhost', true, false, 301, 'https://localhost', ''],
            ['https://localhost', false, false, 200, '', 'max-age=31536000'],
            ['https://localhost', false, true, 200, '', 'max-age=31536000;preload'],
            ['https://localhost', true, false, 200, '', 'max-age=31536000;includeSubDomains'],
            ['https://localhost', true, true, 200, '', 'max-age=31536000;includeSubDomains;preload'],
        ];
    }

    /**
     * @dataProvider httpsProvider
     */
    public function testHttps(
        string $uri,
        bool $includeSubdomains,
        bool $preload,
        int $status,
        string $location,
        string $hsts
    ): void {
        $request = Factory::createServerRequest('GET', $uri);

        $response = Dispatcher::run([
            (new Https())
                ->preload($preload)
                ->includeSubdomains($includeSubdomains),
        ], $request);

        $this->assertEquals($status, $response->getStatusCode());
        $this->assertEquals($location, $response->getHeaderLine('Location'));
        $this->assertEquals($hsts, $response->getHeaderLine('Strict-Transport-Security'));
    }

    public function testRedirectSchemeMatchesPort(): void
    {
        $request = Factory::createServerRequest('GET', 'http://domain.com:80');

        $response = Dispatcher::run([
            (new Https())->includeSubdomains(false),
        ], $request);

        $expectedLocation = 'https://domain.com';
        $location = $response->getHeaderLine('Location');
        $this->assertEquals($expectedLocation, $location);
    }

    public function testCheckHttpsForward(): void
    {
        $request = Factory::createServerRequest('GET', 'http://domain.com:80')
            ->withHeader('X-Forwarded-Proto', 'https');

        $response = Dispatcher::run([
            (new Https())
                ->includeSubdomains(false)
                ->checkHttpsForward(true),
        ], $request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return array<array<string>>
     */
    public function redirectionProvider(): array
    {
        return [
            ['http://domain.com/index', 'https://domain.com/index'],
            ['https://domain.com/index', 'https://domain.com/index'],
            ['http://example.com/index', 'http://example.com/index'],
            ['//example.com/index', '//example.com/index'],
        ];
    }

    /**
     * @dataProvider redirectionProvider
     */
    public function testRedirectScheme(string $uri, string $expected): void
    {
        $request = Factory::createServerRequest('GET', 'https://domain.com');

        $response = Dispatcher::run([
            (new Https())->includeSubdomains(false),
            function ($request) use ($uri) {
                return Factory::createResponse(301)->withHeader('Location', $uri);
            },
        ], $request);

        $this->assertEquals($expected, $response->getHeaderLine('Location'));
    }

    public function testCustomMaxAge(): void
    {
        $response = Dispatcher::run(
            [
                (new Https())->maxAge(10),
            ],
            Factory::createServerRequest('GET', 'https://domain.com')
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('max-age=10', $response->getHeaderLine('Strict-Transport-Security'));
    }

    public function testRedirect(): void
    {
        $request = Factory::createServerRequest('GET', 'http://domain.com');

        $response = Dispatcher::run([
            new Https(),
            function () {
                return Factory::createResponse();
            },
        ], $request);

        $this->assertEquals('https://domain.com', $response->getHeaderLine('Location'));
    }

    public function testRedirectPath(): void
    {
        $request = Factory::createServerRequest('GET', 'https://domain.com');

        $response = Dispatcher::run([
            new Https(),
            function () {
                return Factory::createResponse()->withHeader('Location', '/path');
            },
        ], $request);

        $this->assertEquals('/path', $response->getHeaderLine('Location'));
    }

    public function testNoRedirect(): void
    {
        $request = Factory::createServerRequest('GET', 'http://domain.com');

        $response = Dispatcher::run([
            (new Https())->redirect(false),
            function () {
                return Factory::createResponse();
            },
        ], $request);

        $this->assertFalse($response->hasHeader('Location'));
        $this->assertEquals(200, $response->getStatusCode());
    }
}
