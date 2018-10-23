<?php
declare(strict_types = 1);

namespace Middlewares;

use Middlewares\Utils\Factory;
use Middlewares\Utils\Traits\HasResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Https implements MiddlewareInterface
{
    use HasResponseFactory;

    const HEADER = 'Strict-Transport-Security';

    /**
     * @var int One year by default
     */
    private $maxAge = 31536000;

    /**
     * @var bool Whether add the preload directive or not
     */
    private $preload = false;

    /**
     * @var bool Whether include subdomains
     */
    private $includeSubdomains = false;

    /**
     * @var bool Whether check the headers "X-Forwarded-Proto: https" or "X-Forwarded-Port: 443"
     */
    private $checkHttpsForward = false;

    /**
     * @var bool Whether to redirect on headers check
     */
    private $redirect = true;

    public function __construct(ResponseFactoryInterface $responseFactory = null)
    {
        $this->responseFactory = $responseFactory ?: Factory::getResponseFactory();
    }

    /**
     * Configure the max-age HSTS in seconds.
     */
    public function maxAge(int $maxAge): self
    {
        $this->maxAge = $maxAge;

        return $this;
    }

    /**
     * Configure the includeSubDomains HSTS directive.
     */
    public function includeSubdomains(bool $includeSubdomains = true): self
    {
        $this->includeSubdomains = $includeSubdomains;

        return $this;
    }

    /**
     * Configure the preload HSTS directive.
     */
    public function preload(bool $preload = true): self
    {
        $this->preload = $preload;

        return $this;
    }

    /**
     * Configure whether check the following headers before redirect:
     * X-Forwarded-Proto: https
     * X-Forwarded-Port: 443.
     */
    public function checkHttpsForward(bool $checkHttpsForward = true): self
    {
        $this->checkHttpsForward = $checkHttpsForward;

        return $this;
    }

    /**
     * Enabled or disable redirecting all together.
     */
    public function redirect(bool $redirect = true): self
    {
        $this->redirect = $redirect;

        return $this;
    }

    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();

        if (strtolower($uri->getScheme()) !== 'https') {
            if ($this->mustRedirect($request)) {
                return $this->createResponse(301)
                    ->withHeader('Location', (string) self::withHttps($uri));
            }

            $request = $request->withUri(self::withHttps($uri));
        }

        $response = $handler->handle($request);

        if (!empty($this->maxAge)) {
            $header = sprintf(
                'max-age=%d%s%s',
                $this->maxAge,
                $this->includeSubdomains ? ';includeSubDomains' : '',
                $this->preload ? ';preload' : ''
            );
            $response = $response
                ->withHeader(self::HEADER, $header);
        }

        if ($response->hasHeader('Location')) {
            $location = parse_url($response->getHeaderLine('Location'));

            if (!empty($location['host']) && $location['host'] === $uri->getHost()) {
                $location['scheme'] = 'https';
                unset($location['port']);

                return $response->withHeader('Location', self::unParseUrl($location));
            }
        }

        return $response;
    }

    /**
     * Check whether the request must be redirected or not.
     */
    private function mustRedirect(ServerRequestInterface $request): bool
    {
        if ($this->redirect === false) {
            return false;
        }

        return !$this->checkHttpsForward || (
            $request->getHeaderLine('X-Forwarded-Proto') !== 'https' &&
            $request->getHeaderLine('X-Forwarded-Port') !== '443'
        );
    }

    /**
     * Stringify a url parsed with parse_url()
     */
    private function unParseUrl(array $url): string
    {
        $scheme = isset($url['scheme']) ? $url['scheme'] . '://' : '';
        $host = isset($url['host']) ? $url['host'] : '';
        $port = isset($url['port']) ? ':' . $url['port'] : '';
        $user = isset($url['user']) ? $url['user'] : '';
        $pass = isset($url['pass']) ? ':' . $url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($url['path']) ? $url['path'] : '';
        $query = isset($url['query']) ? '?' . $url['query'] : '';
        $fragment = isset($url['fragment']) ? '#' . $url['fragment'] : '';

        return "{$scheme}{$user}{$pass}{$host}{$port}{$path}{$query}{$fragment}";
    }

    /**
     * Converts a http uri to https.
     */
    private static function withHttps(UriInterface $uri): UriInterface
    {
        return $uri->withScheme('https')->withPort(443);
    }
}
