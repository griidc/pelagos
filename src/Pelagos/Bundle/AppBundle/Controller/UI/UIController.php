<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The default controller for the Pelagos UI App Bundle.
 */
abstract class UIController extends Controller
{
    /**
     * Proteced entityHandler value instance of entityHandler.
     *
     * @var entityHandler
     */
    protected $entityHandler;

    /**
     * Constructor UI Controller.
     *
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->entityHandler = $this->get('pelagos.entity.handler');
    }
}
