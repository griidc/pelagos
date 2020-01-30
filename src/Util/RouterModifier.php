<?php

namespace App\Util;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;


class RouterModifier implements RouterInterface {
    
    protected $innerRouter;
    
    public function __construct(RouterInterface $innerRouter) {
        $this->innerRouter = $innerRouter;
    }
    
    public function setContext(RequestContext $context)
    {
        $this->innerRouter->setContext($context);
    }
    
    public function getContext()
    {
        return $this->innerRouter->getContext();
    }
    
    public function getRouteCollection()
    {
        return $this->innerRouter->getRouteCollection();
    }
    
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        $removeContextPos = strpos($name,'.removeBaseUrl');
        if ($removeContextPos) {
            $context = $this->getContext();
            $context->setBaseUrl('');
            $context = $this->setContext($context);
            $name = substr($name, 0, $removeContextPos);
        }
        
        return $this->innerRouter->generate($name, $parameters, $referenceType);
    }
    
    public function match($pathinfo)
    {
        $parameters = $this->innerRouter->match($pathinfo);
        return $parameters;
    }
}