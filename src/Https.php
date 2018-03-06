<?php
declare(strict_types = 1);

namespace Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Https implements MiddlewareInterface
{
    const HEADER = 'Strict-Transport-Security';

    /**
     * @param int One year by default
     */
    private $maxAge = 31536000;

    /**
     * @param bool Whether add the preload directive or not
     */
    private $preload = false;

    /**
     * @param bool Whether include subdomains
     */
    private $includeSubdomains = false;

    /**
     * @param bool Whether check the headers "X-Forwarded-Proto: https" or "X-Forwarded-Port: 443"
     */
    private $checkHttpsForward = false;

    /**
     * @var bool Whether to redirect on headers check
     */
    private $redirect = true;

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
                return Utils\Factory::createResponse(301)
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
            $location = Utils\Factory::createUri($response->getHeaderLine('Location'));

            if ($location->getHost() === '' || $location->getHost() === $uri->getHost()) {
                return $response->withHeader('Location', (string) self::withHttps($location));
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
     * Converts a http uri to https.
     */
    private static function withHttps(UriInterface $uri): UriInterface
    {
        return $uri->withScheme('https')->withPort(443);
    }
}
