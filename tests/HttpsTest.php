<?php

namespace Middlewares\Tests;

use Middlewares\Https;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;

class HttpsTest extends \PHPUnit_Framework_TestCase
{
    public function httpsProvider()
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
    public function testHttps($uri, $includeSubdomains, $preload, $status, $location, $hsts)
    {
        $request = Factory::createServerRequest([], 'GET', $uri);

        $response = Dispatcher::run([
            (new Https())
                ->preload($preload)
                ->includeSubdomains($includeSubdomains),
        ], $request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertEquals($location, $response->getHeaderLine('Location'));
        $this->assertEquals($hsts, $response->getHeaderLine('Strict-Transport-Security'));
    }

    public function testRedirectSchemeMatchesPort()
    {
        $request = Factory::createServerRequest([], 'GET', 'http://domain.com:80');

        $response = Dispatcher::run([
            (new Https())->includeSubdomains(false),
        ], $request);

        $expectedLocation = 'https://domain.com';
        $location = $response->getHeaderLine('Location');
        $this->assertEquals($expectedLocation, $location);
    }

    public function testCheckHttpsForward()
    {
        $request = Factory::createServerRequest([], 'GET', 'http://domain.com:80')
            ->withHeader('X-Forwarded-Proto', 'https');

        $response = Dispatcher::run([
            (new Https())
                ->includeSubdomains(false)
                ->checkHttpsForward(true),
        ], $request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function redirectionProvider()
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
    public function testRedirectScheme($uri, $expected)
    {
        $request = Factory::createServerRequest([], 'GET', 'https://domain.com');

        $response = Dispatcher::run([
            (new Https())->includeSubdomains(false),
            function ($request) use ($uri) {
                return Factory::createResponse(301)->withHeader('Location', $uri);
            },
        ], $request);

        $this->assertEquals($expected, $response->getHeaderLine('Location'));
    }
}
