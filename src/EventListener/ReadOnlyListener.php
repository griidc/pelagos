<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

/**
 * Class ReadOnlyListener to enable/diable maintenance mode.
 */
class ReadOnlyListener
{
    /**
     * Boolean value for read only  mode.
     *
     * @var boolean
     */
    protected $readOnly;

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
     * @param boolean     $readOnly Boolean value to check if it is in read only mode.
     * @param boolean     $debug    Boolean value to check if it is in debug.
     * @param Environment $twig     Twig environment variable.
     */
    public function __construct(bool $readOnly, bool $debug, Environment $twig)
    {
        $this->readOnly = $readOnly;
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
        if ($this->readOnly and !$this->debug) {
            $template = $this->twig->render('template/readonly.html.twig');
            $event->setResponse(new Response($template, 503));
            $event->stopPropagation();
        }
    }
}
