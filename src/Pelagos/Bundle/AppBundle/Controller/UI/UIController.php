<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Pelagos\Entity\Entity;

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
     * Set Container function, to add to container.
     *
     * @param ContainerInterface $container The container for the UIController.
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->entityHandler = $this->get('pelagos.entity.handler');
    }

    /**
     * Validates the Entity prior to persisting it.
     *
     * @param Entity $entity The Entity (and it's extentions).
     *
     * @throws BadRequestHttpException When invalid data is submitted.
     *
     * @return void
     */
    public function validateEntity(Entity $entity)
    {
        $validator = $this->get('validator');
        $errors = $validator->validate($entity);

        if (count($errors) > 0) {
            throw new BadRequestHttpException(
                (string) $errors
            );
        }
    }
}
