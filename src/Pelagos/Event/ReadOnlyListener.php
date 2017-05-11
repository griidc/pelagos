<?php

namespace Pelagos\Event;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Pelagos\Bundle\AppBundle\Controller\UI\OptionalReadOnlyInterface;

/**
 * Class for read-only event implementation.
 */
class ReadOnlyListener
{
    /**
     * Boolean flag to hold read-only mode status.
     *
     * @var boolean
     */
    protected $readOnlyModeFlag;

    /**
     * Class constructor.
     *
     * @param boolean $readOnlyModeFlag Signifies read-only status for marked controllers.
     */
    public function __construct($readOnlyModeFlag)
    {
        $this->readOnlyFlag = $readOnlyModeFlag;
    }

    /**
     * This event fires prior to any controller start.
     *
     * @param FilterControllerEvent $event This is an internal passed event.
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof OptionalReadOnlyInterface) {
            if (true === $this->readOnlyFlag) {
                die("We're in Read-Only Mode dude.");
            }
        }
    }
}
