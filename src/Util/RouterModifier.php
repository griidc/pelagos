<?php

namespace App\Util;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * This class is a custom router modifier.
 */
class RouterModifier implements RouterInterface
{
    /**
     * The original (inner) Router Interface.
     *
     * @var RouterInterface
     */
    protected $innerRouter;

    /**
     * The list of routes to have their baseUrl removed.
     *
     * @var array
     */
    protected $excludeRoutes;

    /**
     * Constructor.
     *
     * @param RouterInterface $innerRouter The inner Router Interface.
     */
    public function __construct(RouterInterface $innerRouter, array $excludeRoutes)
    {
        $this->innerRouter = $innerRouter;
        $this->excludeRoutes = $excludeRoutes;
    }

    /**
     * RequestContext setter.
     *
     * @param RequestContext $context The Request Context.
     *
     * @return void
     */
    public function setContext(RequestContext $context) : void
    {
        $this->innerRouter->setContext($context);
    }

    /**
     * RequestContext Getter.
     *
     * @return RequestContext
     */
    public function getContext() : RequestContext
    {
        return $this->innerRouter->getContext();
    }

    /**
     * RouteCollection Getter.
     *
     * @return RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->innerRouter->getRouteCollection();
    }

    /**
     * URL Generator.
     *
     * @param mixed $name          The Request Context.
     * @param mixed $parameters    The Request Context.
     * @param mixed $referenceType The Request Context.
     *
     * @return string
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH) : string
    {
        if (in_array($name, $this->excludeRoutes)) {
            $context = $this->getContext();
            $oldContext = $context;
            $context->setBaseUrl('');
            $context = $this->setContext($context);
            $generate = $this->innerRouter->generate($name, $parameters, $referenceType);
            $context = $this->setContext($oldContext);
        } else {
            $generate = $this->innerRouter->generate($name, $parameters, $referenceType);
        }

        return $generate;
    }

    /**
     * Url Matcher.
     *
     * @param mixed $pathinfo The path.
     *
     * @return array
     */
    public function match($pathinfo) : array
    {
        return $this->innerRouter->match($pathinfo);
    }
}
