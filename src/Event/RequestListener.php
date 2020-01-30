<?php

namespace App\Event;

use Symfony\Component\HttpKernel\Event\RequestEvent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContextAwareInterface;

class RequestListener
{
     /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var RequestContextAwareInterface
     */
    private $router;

    /**
     * @param TokenStorageInterface        $tokenStorage
     * @param RequestContextAwareInterface $router
     */
    public function __construct(RequestContextAwareInterface $router)
    {
        $this->router = $router;
    }
    
    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        // ...
        
        // $request = $event->getRequest();
        
        // $context = new RequestContext();
        // $context->fromRequest($request);
        
        // $context->setBaseUrl('/blaaa/');
        // https://stackoverflow.com/questions/22510569/override-router-and-add-parameter-to-specific-routes-before-path-url-used/31859779#31859779
        
        // dump($event);
        
        $context = $this->router->getContext();
        
        //$context->setBaseUrl('/booooo/');
        
        // dump($context);
    }
}