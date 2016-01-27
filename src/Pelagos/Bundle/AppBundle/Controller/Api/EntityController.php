<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Form\FormTypeInterface;

use FOS\RestBundle\Controller\FOSRestController;

use Pelagos\Bundle\AppBundle\Exception\InvalidFormException;

use Pelagos\Entity\Entity;

/**
 * The Entity api controller.
 */
abstract class EntityController extends FOSRestController
{
    /**
     * The type of entity this controller is for.
     *
     * @var string
     */
    private $entityType;

    /**
     * Constructor that sets $entityType based on the class name.
     */
    public function __construct()
    {
        preg_match('/([^\\\\]+)Controller$/', get_class($this), $matches);
        $this->entityType = $matches[1];
    }

    /**
     * Get all entities of a given type.
     *
     * @return array
     */
    public function cgetAction()
    {
        $entities = $this
            ->container
            ->get('pelagos.entity.handler')
            ->getAll($this->entityType);
        return $entities;
    }

    /**
     * Get a single entity of a given type identified by $id.
     *
     * @param integer $id The id of the entity to return.
     *
     * @throws BadRequestHttpException When the provided id is not a non-negative integer.
     * @throws NotFoundHttpException   When an entity of a given type identified by $id is not found.
     *
     * @return Entity
     */
    public function getAction($id)
    {
        if (!preg_match('/^\d+$/', $id)) {
            throw new BadRequestHttpException('id must be a non-negative integer');
        }
        $entity = $this
            ->container
            ->get('pelagos.entity.handler')
            ->get($this->entityType, $id);
        if ($entity === null) {
            throw $this->createNotFoundException('No ' . $this->entityType . " exists with id: $id");
        }
        return $entity;
    }

    /**
     * Create an entity from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @return Entity|FormTypeInterface
     */
    public function postAction(Request $request)
    {
        $entityClass = '\Pelagos\Entity\\' . $this->entityType;
        $entity = new $entityClass;
        $entity->setCreator($this->getUser()->getUsername());
        try {
            $this
                ->container
                ->get('pelagos.entity.handler')
                ->post(
                    'Pelagos\Bundle\AppBundle\Form\\' . $this->entityType . 'Type',
                    $entity,
                    $request
                );
            return $entity;
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Presents the form to use to create a new entity.
     *
     * @return FormTypeInterface
     */
    public function newAction()
    {
        return $this->container->get('form.factory')->createNamed(
            null,
            'Pelagos\Bundle\AppBundle\Form\\' . $this->entityType . 'Type',
            null,
            array(
                'action' => $this->generateUrl(
                    'pelagos_api_post_' . ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $this->entityType)), '_')
                ),
                'method' => 'POST',
            )
        );
    }
}
