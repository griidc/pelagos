<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Form\FormTypeInterface;

use FOS\RestBundle\Controller\FOSRestController;

use Pelagos\Bundle\AppBundle\Exception\InvalidFormException;

use Pelagos\Entity\Entity;
use Pelagos\Entity\Account;

/**
 * The Entity api controller.
 */
abstract class EntityController extends FOSRestController
{
    /**
     * Get all entities of a given type.
     *
     * @param string  $entityClass The type of entity.
     * @param Request $request     The request object.
     *
     * @return array
     */
    public function handleGetCollection($entityClass, Request $request)
    {
        $params = $request->query->all();
        if (array_key_exists('q', $params)) {
            // Remove the 'q' parameter if it exists (this comes from Drupal).
            unset($params['q']);
        }
        if (count($params) > 0) {
            return $this->container->get('pelagos.entity.handler')->getBy($entityClass, $params);
        }
        return $this->container->get('pelagos.entity.handler')->getAll($entityClass);
    }

    /**
     * Get a single entity of a given type identified by $id.
     *
     * @param string  $entityClass The type of entity.
     * @param integer $id          The id of the entity.
     *
     * @throws BadRequestHttpException When the provided id is not a non-negative integer.
     * @throws NotFoundHttpException   When an entity of a given type identified by $id is not found.
     *
     * @return Entity
     */
    public function handleGetOne($entityClass, $id)
    {
        if (!preg_match('/^\d+$/', $id)) {
            throw new BadRequestHttpException('id must be a non-negative integer');
        }
        $entity = $this
            ->container
            ->get('pelagos.entity.handler')
            ->get($entityClass, $id);
        if ($entity === null) {
            throw $this->createNotFoundException('No ' . $entityClass::FRIENDLY_NAME . " exists with id: $id");
        }
        return $entity;
    }

    /**
     * Create an entity from the submitted data.
     *
     * @param string  $formType    The type of form.
     * @param string  $entityClass The type of entity.
     * @param Request $request     The request object.
     *
     * @return Entity|FormTypeInterface
     */
    public function handlePost($formType, $entityClass, Request $request)
    {
        $entity = new $entityClass;
        $user = $this->getUser();
        $creator = 'anonymous';
        if ($user instanceof Account) {
            $creator = $user->getUsername();
        }
        $entity->setCreator($creator);
        try {
            $this
                ->container
                ->get('pelagos.entity.handler')
                ->post($formType, $entity, $request);
            return $entity;
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }
}
