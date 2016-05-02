<?php

namespace Pelagos\Bundle\AppBundle\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\Common\Collections\Collection;

use Pelagos\Entity\Entity;
use Pelagos\Entity\Account;
use Pelagos\Entity\Person;

use Pelagos\Event\EntityEvent;

use Pelagos\Exception\UnmappedPropertyException;

use Pelagos\Bundle\AppBundle\Security\PelagosEntityVoter;
use Pelagos\Bundle\AppBundle\Security\EntityProperty;

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
     * The token storage to use in this entity handler.
     *
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * The authorization checker to use in this entity handler.
     *
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * The event dispatcher to use in this entity handler.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Constructor for EntityHandler.
     *
     * @param EntityManager                 $entityManager        The entity manager to use.
     * @param TokenStorageInterface         $tokenStorage         The token storage to use.
     * @param AuthorizationCheckerInterface $authorizationChecker The authorization checker to use.
     * @param EventDispatcherInterface      $eventDispatcher      The event dispatcher to use.
     */
    public function __construct(
        EntityManager $entityManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Return an entity of $entityClass identified by $id.
     *
     * @param string  $entityClass The type of entity to retrieve.
     * @param integer $id          The id of the entity to retrieve.
     *
     * @return Entity|null The entity.
     */
    public function get($entityClass, $id)
    {
        return $this->entityManager
            ->getRepository($entityClass)
            ->find($id);
    }

    /**
     * Return all entities of $entityClass.
     *
     * @param string $entityClass The type of entity to retrieve.
     *
     * @return Collection A collection of entities.
     */
    public function getAll($entityClass)
    {
        return $this->entityManager
            ->getRepository($entityClass)
            ->findAll();
    }

    /**
     * Return all entities of $entityClass filtered by $criteria and sorted by $orderBy.
     *
     * @param string     $entityClass The type of entity to retrieve.
     * @param array      $criteria    The criteria to filter by.
     * @param array|null $orderBy     The properties to sort by.
     *
     * @return Collection A collection of entities.
     */
    public function getBy($entityClass, array $criteria, $orderBy = null)
    {
        // Default to not use query builder.
        $useQb = false;
        // Look through the criteria.
        foreach (array_keys($criteria) as $property) {
            // If a propery contains a . it is a "deep" property and we need to use query builder.
            if (false !== strpos($property, '.')) {
                $useQb = true;
            }
        }
        if ($useQb) {
            $qb = $this->entityManager->createQueryBuilder();
            // Start with a select of the entity type we are querying as 'e'.
            $qb->select('e')
               ->from($entityClass, 'e');
            // Initialize an array to collect parameters.
            $parameters = array();
            // Initialize our parameter tokens at 1.
            $paramToken = 1;
            // Loop through the criteria.
            foreach ($criteria as $property => $value) {
                // If the property contains a dot, capture both sides.
                if (preg_match('/^([^\.]+)\.([^\.]+)$/', $property, $matches)) {
                    list ($descriptor, $property, $propertyProperty) = $matches;
                    // Join the property as '$property' and filter by its property.
                    $qb->join("e.$property", $property)
                       ->andWhere($qb->expr()->eq("$property.$propertyProperty", "?$paramToken"));
                } else {
                    // Otherwise, do a regular filter.
                    $qb->andWhere($qb->expr()->eq("e.$property", "?$paramToken"));
                }
                // Add the value to the parameter array.
                $parameters[$paramToken] = $value;
                // Increment our parameter token counter;
                $paramToken++;
            }
            // If we've specified an order by.
            if (null !== $orderBy) {
                // Loop through the properties.
                foreach ($orderBy as $property => $order) {
                    // If the property contains a dot, capture both sides.
                    if (preg_match('/^([^\.]+)\.([^\.]+)$/', $property, $matches)) {
                        list ($descriptor, $property, $propertyProperty) = $matches;
                        // Order by 'e2's property.
                        $qb->orderBy("$property.$propertyProperty", $order);
                    } else {
                        // Otherwise do a regular order by.
                        $qb->orderBy("e.$property", $order);
                    }
                }
            }
            // Set the parameters from the parameter array.
            $qb->setParameters($parameters);
            // Get the query.
            $query = $qb->getQuery();
            // Return the result.
            return $query->getResult();
        }
        // If we don't need to use query builder, just use findBy.
        return $this->entityManager
            ->getRepository($entityClass)
            ->findBy($criteria, $orderBy);
    }

    /**
     * Create a new entity.
     *
     * @param Entity      $entity          The entity to create.
     * @param string|null $entityEventName The name of the entity event to dispatch (default: 'created').
     *
     * @throws AccessDeniedException When the user does not have sufficient privileges to create the entity.
     *
     * @return Entity The new entity.
     */
    public function create(Entity $entity, $entityEventName = 'created')
    {
        if (!$this->authorizationChecker->isGranted('CAN_CREATE', $entity)) {
            throw new AccessDeniedException(
                'You do not have sufficient privileges to create this ' . $entity::FRIENDLY_NAME . '.'
            );
        }
        // Set the creator to the currently authenticated user.
        $entity->setCreator($this->getAuthenticatedPerson());
        // Get the id.
        $id = $entity->getId();
        // Get the class metadata for this entity.
        $metadata = $this->entityManager->getClassMetaData(get_class($entity));
        // Save the original ID generator.
        $idGenerator = $metadata->idGenerator;
        // If the entity has been manually assigned an ID.
        if ($id !== null) {
            // Temporarily change the ID generator to AssignedGenerator.
            $metadata->setIdGenerator(new AssignedGenerator());
        }
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
        // If the entity has been manually assigned an ID.
        if ($id !== null) {
            // Restore the original ID generator for entities of this class.
            $metadata->setIdGenerator($idGenerator);
        }
        $this->dispatchEntityEvent($entity, $entityEventName);
        return $entity;
    }

    /**
     * Update an entity.
     *
     * @param Entity      $entity          The entity to update.
     * @param string|null $entityEventName The name of the entity event to dispatch (default: 'updated').
     *
     * @throws AccessDeniedException When the user does not have sufficient privileges to update the entity.
     *
     * @return Entity The updated entity.
     */
    public function update(Entity $entity, $entityEventName = 'updated')
    {
        if (!$this->authorizationChecker->isGranted(PelagosEntityVoter::CAN_EDIT, $entity)) {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($entity);
            foreach (array_keys($changeSet) as $property) {
                $entityProperty = new EntityProperty($entity, $property);
                if (!$this->authorizationChecker->isGranted(PelagosEntityVoter::CAN_EDIT, $entityProperty)) {
                    throw new AccessDeniedException(
                        'You do not have sufficient privileges to edit this ' . $entity::FRIENDLY_NAME . '.'
                    );
                }
            }
        }
        // Set the modifier to the currently authenticated user.
        $entity->setModifier($this->getAuthenticatedPerson());
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
        $this->dispatchEntityEvent($entity, $entityEventName);
        return $entity;
    }

    /**
     * Delete an entity.
     *
     * @param Entity      $entity          The entity object to delete.
     * @param string|null $entityEventName The name of the entity event to dispatch (default: 'deleted').
     *
     * @throws AccessDeniedException When the user does not have sufficient privileges to delete the entity.
     *
     * @return Entity The entity object that was deleted.
     */
    public function delete(Entity $entity, $entityEventName = 'deleted')
    {
        if (!$this->authorizationChecker->isGranted(PelagosEntityVoter::CAN_DELETE, $entity)) {
            throw new AccessDeniedException(
                'You do not have sufficient privileges to delete this ' . $entity::FRIENDLY_NAME . '.'
            );
        }
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        $this->dispatchEntityEvent($entity, $entityEventName);
        return $entity;
    }

    /**
     * Get a list of all distinct values for a property of a given Entity class.
     *
     * @param string $entityClass Entity class to get distinct values from.
     * @param string $property    Property to get distinct values of.
     *
     * @throws AccessDeniedException     When the user does not have sufficient privileges to get
     *                                   a list of distinct values for properties of the entity.
     * @throws UnmappedPropertyException When Entity $entityClass does not have a mapped property $property.
     *
     * @return array List of all distinct values for $property for $entityClass.
     */
    public function getDistinctVals($entityClass, $property)
    {
        $entity = new $entityClass;
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException(
                'Only authenticated users may retrieve a list of distinct values ' .
                "for property $property of " . $entity::FRIENDLY_NAME . '.'
            );
        }
        $class = $this->entityManager->getClassMetadata($entityClass);
        if (!$class->hasField($property) && !$class->hasAssociation($property)) {
            $exception = new UnmappedPropertyException;
            $exception->setClassName($entityClass);
            $exception->setPropertyName($property);
            throw $exception;
        }
        $this->entityManager
            ->getConfiguration()
            ->addCustomHydrationMode(
                'COLUMN_HYDRATOR',
                'Pelagos\DoctrineExtensions\Hydrators\ColumnHydrator'
            );
        // Get distinct vals
        $query = $this->entityManager
            ->getRepository($entityClass)
            ->createQueryBuilder('entity')
            ->select("entity.$property")
            ->where("entity.$property IS NOT NULL")
            ->distinct()
            ->orderBy("entity.$property")
            ->getQuery();
        return $query->getResult('COLUMN_HYDRATOR');
    }

    /**
     * Dispatch an Entity event.
     *
     * @param Entity $entity          The Entity the event is for.
     * @param string $entityEventName The name of the entity event.
     *
     * @return void
     */
    public function dispatchEntityEvent(Entity $entity, $entityEventName)
    {
        $this->eventDispatcher->dispatch(
            'pelagos.entity.' . $entity->getUnderscoredName() . '.' . $entityEventName,
            new EntityEvent($entity)
        );
    }

    /**
     * Get the currently authenticated Person.
     *
     * @return Person The currently authenticated Person.
     */
    protected function getAuthenticatedPerson()
    {
        $user = $this->tokenStorage->getToken()->getUser();
        // If user is authenticated.
        if ($user instanceof Account) {
            // Return the authenticated person.
            return $user->getPerson();
        }
        // Return the anonymous person by default.
        return $this->get(Person::class, -1);
    }
}
