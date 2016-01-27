<?php

namespace Pelagos\Bundle\AppBundle\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
     * The authorization checker to use in this entity handler.
     *
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * Constructor for EntityHandler.
     *
     * @param EntityManager                 $entityManager        The entity manager to use.
     * @param FormFactoryInterface          $formFactory          The form factory to use.
     * @param AuthorizationCheckerInterface $authorizationChecker The authorization checker to use.
     */
    public function __construct(
        EntityManager $entityManager,
        FormFactoryInterface $formFactory,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->authorizationChecker = $authorizationChecker;
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
     * @throws InvalidFormException  When invalid data is submitted.
     * @throws AccessDeniedException When the user does not have sufficient privileges to create the entity.
     *
     * @return Entity The updated entity.
     */
    private function processForm($formType, Entity $entity, Request $request, $method = 'PUT')
    {
        $form = $this->formFactory->createNamed(null, $formType, $entity, array('method' => $method));
        $form->handleRequest($request);
        if ($form->isValid()) {
            if (!$this->authorizationChecker->isGranted('CAN_CREATE', $entity)) {
                $action = 'edit';
                if ($method == 'POST') {
                    $action = 'create';
                }
                $entityType = substr(strrchr(get_class($entity), '\\'), 1);
                throw new AccessDeniedException("You do not have sufficient privileges to $action this $entityType.");
            }
            foreach ($request->files->all() as $property => $file) {
                if (isset($file)) {
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
