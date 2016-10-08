<?php

namespace Middlewares\Tests;

use Middlewares\Https;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use mindplay\middleman\Dispatcher;

class HttpsTest extends \PHPUnit_Framework_TestCase
{
    public function httpsProvider()
    {
        return [
            ['http://localhost', true, 301, 'https://localhost', ''],
            ['https://localhost', false, 200, '', 'max-age=31536000'],
            ['https://localhost', true, 200, '', 'max-age=31536000;includeSubDomains'],
        ];
    }

    /**
     * @dataProvider httpsProvider
     */
    public function testHttps($uri, $includeSubdomains, $status, $location, $hsts)
    {
        $response = (new Dispatcher([
            (new Https())->includeSubdomains($includeSubdomains),
            function () {
                return new Response();
            },
        ]))->dispatch(new Request($uri));

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertEquals($location, $response->getHeaderLine('Location'));
        $this->assertEquals($hsts, $response->getHeaderLine('Strict-Transport-Security'));
    }

    public function testRedirectSchemeMatchesPort()
    {
        $response = (new Dispatcher([
            (new Https())->includeSubdomains(false),
        ]))->dispatch(new Request('http://domain.com:80'));

        $expectedLocation = 'https://domain.com';
        $location = $response->getHeaderLine('Location');
        $this->assertEquals($expectedLocation, $location);
    }

    public function testCheckHttpsForward()
    {
        $request = (new Request('http://domain.com:80'))
            ->withHeader('X-Forwarded-Proto', 'https');

        $response = (new Dispatcher([
            (new Https())
                ->includeSubdomains(false)
                ->checkHttpsForward(true),
            function () {
                return new Response();
            },
        ]))->dispatch($request);

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
        $response = (new Dispatcher([
            (new Https())->includeSubdomains(false),
            function ($request) use ($uri) {
                return (new Response())->withStatus(301)->withHeader('Location', $uri);
            },
        ]))->dispatch(new Request('https://domain.com'));

        $this->assertEquals($expected, $response->getHeaderLine('Location'));
    }
}
