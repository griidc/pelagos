<?php

namespace Pelagos\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router as SymfonyRouter;
use Symfony\Component\Routing\RouterInterface;

/**
 * Pelagos router that decorates Symfony's router.
 *
 * @see Symfony\Component\Routing\RouterInterface
 * @see Symfony\Component\Routing\Matcher\RequestMatcherInterface
 */
class Router implements RouterInterface, RequestMatcherInterface
{
    /**
     * Symfony's router.
     *
     * @var SymfonyRouter
     */
    protected $symfonyRouter;

    /**
     * The Pelagos URL prefix.
     *
     * @var string
     */
    protected $pelagosPrefix;

    /**
     * Constructor.
     *
     * @param SymfonyRouter $symfonyRouter Symfony's router.
     * @param string        $pelagosPrefix The Pelagos URL prefix.
     */
    public function __construct(SymfonyRouter $symfonyRouter, $pelagosPrefix)
    {
        $this->symfonyRouter = $symfonyRouter;
        $this->pelagosPrefix = $pelagosPrefix;
    }

    /**
     * Gets the RouteCollection instance associated with this Router.
     *
     * @see Symfony\Component\Routing\RouterInterface
     *
     * @return RouteCollection A RouteCollection instance.
     */
    public function getRouteCollection()
    {
        return $this->symfonyRouter->getRouteCollection();
    }

    /**
     * Sets the request context.
     *
     * @param RequestContext $context The context.
     *
     * @see Symfony\Component\Routing\RequestContextAwareInterface
     *
     * @return void
     */
    public function setContext(RequestContext $context)
    {
        $this->symfonyRouter->setContext($context);
    }

    /**
     * Gets the request context.
     *
     * @see Symfony\Component\Routing\RequestContextAwareInterface
     *
     * @return RequestContext The context.
     */
    public function getContext()
    {
        return $this->symfonyRouter->getContext();
    }

    /**
     * Generates a URL or path for a specific route based on the given parameters.
     *
     * @param string  $name          The name of the route.
     * @param mixed   $parameters    An array of parameters.
     * @param integer $referenceType The type of reference to be generated (one of the constants).
     *
     * @see Symfony\Component\Routing\Generator\UrlGeneratorInterface
     *
     * @return The generated URL.
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        // Use Symfony's router to generate a URL.
        $url = $this->symfonyRouter->generate($name, $parameters, $referenceType);
        // If aliases have been defined in an environment variable.
        if (array_key_exists('PELAGOS_ALIASES', $_SERVER)) {
            // Decode them into an array.
            $aliases = json_decode($_SERVER['PELAGOS_ALIASES'], true);
            // Match the first URL path segment after the Pelagos prefix.
            if (preg_match('!^(?:https?://[^/]+)?/' . $this->pelagosPrefix . '/([^/]+)!', $url, $matches)) {
                // If aliases decoded as an array and the first URL path segment is found in aliases.
                if (is_array($aliases) and array_key_exists($matches[1], $aliases)) {
                    // Replace the Pelagos prefix and first URL path segment with the alias.
                    $url = preg_replace(
                        '!^(https?://[^/]+)?/' . $this->pelagosPrefix . '/[^/]+!',
                        '\1' . $aliases[$matches[1]],
                        $url
                    );
                }
            }
        }
        return $url;
    }

    /**
     * Tries to match a URL path with a set of routes.
     *
     * @param string $pathinfo The path info to be parsed (raw format, i.e. not urldecoded).
     *
     * @see Symfony\Component\Routing\Matcher\UrlMatcherInterface
     *
     * @return array An array of parameters.
     */
    public function match($pathinfo)
    {
        return $this->symfonyRouter->match($pathinfo);
    }

    /**
     * Tries to match a request with a set of routes.
     *
     * @param Request $request The request to match.
     *
     * @see Symfony\Component\Routing\Matcher\RequestMatcherInterface
     *
     * @return array An array of parameters.
     */
    public function matchRequest(Request $request)
    {
        return $this->symfonyRouter->matchRequest($request);
    }
}
