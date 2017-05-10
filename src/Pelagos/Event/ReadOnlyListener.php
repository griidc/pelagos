<?php

namespace Pelagos\Event;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Pelagos\Bundle\AppBundle\Controller\UI\OptionalReadOnlyInterface;

/**
 * Class for read-only event implementation.
 */
class OptionalReadOnlyListener
{
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof OptionalReadOnlyInterface) {
            #if ($this->container->hasParameter('pelagos_readonly_mode') and $this->container->getParameter('pelagos_readonly_mode') == true) {
                die("We're in Read-Only Mode dude.");
            #}
        }
    }
}
