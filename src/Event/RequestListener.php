<?php

namespace App\Event;

use Symfony\Component\HttpKernel\Event\RequestEvent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;



class RequestListener
{
    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        // ...
        
        $request = $event->getRequest();
        
        $context = new RequestContext();
        $context->fromRequest($request);
        
        $context->setBaseUrl('/blaaa/');
        
        dump($context);
    }
}