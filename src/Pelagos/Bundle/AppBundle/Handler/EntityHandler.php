<?php

namespace Pelagos\Bundle\AppBundle\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Collection;

use Pelagos\Entity\Entity;
use Pelagos\Bundle\AppBundle\Form\PersonType;
use Pelagos\Bundle\AppBundle\Exception\InvalidFormException;

/**
 * A handler for entities.
 */
class EntityHandler
{
    /**
     * The entity manager to use in this entity handler.
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * The form factory to use in this entity handler.
     *
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * Constructor for EntityHandler.
     *
     * @param EntityManager        $entityManager The entity manager to use in this entity handler.
     * @param FormFactoryInterface $formFactory   The form factory to use in this entity handler.
     */
    public function __construct(EntityManager $entityManager, FormFactoryInterface $formFactory)
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    /**
     * Return an entity of $entityType identified by $id.
     *
     * @param string  $entityType The type of entity to retrieve.
     * @param integer $id         The id of the entity to retrieve.
     *
     * @return Entity|null The entity.
     */
    public function get($entityType, $id)
    {
        return $this->entityManager
            ->getRepository('Pelagos:' . $entityType)
            ->find($id);
    }

    /**
     * Return all entities of $entityType.
     *
     * @param string $entityType The type of entity to retrieve.
     *
     * @return Collection A collection of entities.
     */
    public function getAll($entityType)
    {
        return $this->entityManager
            ->getRepository('Pelagos:' . $entityType)
            ->findAll();
    }

    /**
     * Handle a post of a new entity.
     *
     * @param string  $formType The type of form to process.
     * @param Entity  $entity   The entity to populate.
     * @param Request $request  The request object.
     *
     * @return Entity The updated entity.
     */
    public function post($formType, Entity $entity, Request $request)
    {
        return $this->processForm($formType, $entity, $request, 'POST');
    }

    /**
     * Processes the form.
     *
     * @param string  $formType The type of form to process.
     * @param Entity  $entity   The entity to populate.
     * @param Request $request  The request object.
     * @param string  $method   The HTTP method.
     *
     * @throws InvalidFormException When invalid data is submitted.
     *
     * @return Entity The updated entity.
     */
    private function processForm($formType, Entity $entity, Request $request, $method = 'PUT')
    {
        $form = $this->formFactory->create($formType, $entity, array('method' => $method));
        $form->handleRequest($request);
        if ($form->isValid()) {
            foreach ($request->files->all() as $type => $files) {
                foreach ($files as $property => $file) {
                    $setter = 'set' . ucfirst($property);
                    $entity->$setter(file_get_contents($file->getPathname()));
                }
            }
            $this->entityManager->persist($entity);
            $this->entityManager->flush($entity);
            return $entity;
        }
        throw new InvalidFormException('Invalid submitted data', $form);
    }
}
