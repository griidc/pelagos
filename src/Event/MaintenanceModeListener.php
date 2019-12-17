<?php

namespace App\Event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

/**
 * Class MaintenanceModeListener to enable/diable maintenance mode.
 */
class MaintenanceModeListener
{
    /**
     * Boolean value for maintenance mode.
     *
     * @var boolean
     */
    protected $maintenanceMode;

    /**
     * Boolean value for debug mode.
     *
     * @var boolean
     */
    protected $debug;

    /**
     * The twig templating engine instance.
     *
     * @var Environment
     */
    protected $twig;

    /**
     * ReadOnlyListener constructor.
     *
     * @param boolean     $maintenanceMode Boolean value to check if it is in read only mode.
     * @param boolean     $debug           Boolean value to check if it is in debug.
     * @param Environment $twig            Twig environment variable.
     */
    public function __construct(bool $maintenanceMode, bool $debug, Environment $twig)
    {
        $this->maintenanceMode = $maintenanceMode;
        $this->debug = $debug;
        $this->twig = $twig;
    }

    /**
     * Kernel event listener method to enable/disbale maintenance mode.
     *
     * @param RequestEvent $event Symfony event variable.
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if ($this->maintenanceMode and !$this->debug) {
            $template = $this->twig->render('template/maintenanceMode.html.twig');
            $event->setResponse(new Response($template, 503));
            $event->stopPropagation();
        }
    }
}
