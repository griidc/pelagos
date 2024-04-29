<?php

namespace App\Handler;

use App\Entity\Entity;
use App\Entity\Account;
use App\Entity\Dataset;
use App\Entity\Password;
use App\Entity\Person;
use App\Entity\ResearchGroup;
use App\Event\EntityEventDispatcher;
use App\Exception\UnmappedPropertyException;
use App\Security\Voter\PelagosEntityVoter;
use App\Security\EntityProperty;
use App\Util\FundingOrgFilter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

/**
 * A handler for entities.
 *
 * @deprecated 6.57.0 Entityhandler should not be used anymore, use Repository or EntityManager
 */
class EntityHandler
{
    /**
     * The entity manager to use in this entity handler.
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * The Symfony Security component.
     *
     * @var Security
     */
    private $security;

    /**
     * The authorization checker to use in this entity handler.
     *
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * The entity event dispatcher.
     *
     * @var EntityEventDispatcher
     */
    private $entityEventDispatcher;

    /**
     * A list of entities that are proctected and not accessible in collections.
     *
     * @var array
     */
    private $protectedEntities = array(
        Account::class,
        Password::class,
    );

    /**
     * Utility to filter by funding organization.
     *
     * @var FundingOrgFilter
     */
    private $fundingOrgFilter;

    /**
     * Constructor for EntityHandler.
     *
     * @param EntityManagerInterface        $entityManager         The entity manager to use.
     * @param Security                      $security              The Symfony Security component.
     * @param AuthorizationCheckerInterface $authorizationChecker  The authorization checker to use.
     * @param EntityEventDispatcher         $entityEventDispatcher The entity event dispatcher.
     * @param FundingOrgFilter              $fundingOrgFilter      Utility to filter by funding organization.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityEventDispatcher $entityEventDispatcher,
        FundingOrgFilter $fundingOrgFilter
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->authorizationChecker = $authorizationChecker;
        $this->entityEventDispatcher = $entityEventDispatcher;
        $this->fundingOrgFilter = $fundingOrgFilter;
    }

    /**
     * Return an entity of $entityClass identified by $id.
     *
     * @param string  $entityClass The type of entity to retrieve.
     * @param integer $id          The id of the entity to retrieve.
     *
     * @return Entity|null The entity.
     */
    public function get(string $entityClass, int $id)
    {
        return $this->entityManager
            ->getRepository($entityClass)
            ->find($id);
    }

    /**
     * Return all entities of $entityClass.
     *
     * @param string       $entityClass The type of entity to retrieve.
     * @param array        $orderBy     The properties to sort by.
     * @param array        $properties  The properties to hydrate.
     * @param integer|null $hydrator    The hydrator to use or null for the default hydrator
     *                                  (see Query::HYDRATE_* constants).
     *
     * @return Collection|array A collection of entities or an array depending on the hydrator.
     */
    public function getAll(
        string $entityClass,
        array $orderBy = array(),
        array $properties = array(),
        $hydrator = null
    ) {
        // Just call getBy with no criteria.
        return $this->getBy($entityClass, array(), $orderBy, $properties, $hydrator);
    }

    /**
     * Return all entities of $entityClass filtered by $criteria and sorted by $orderBy.
     *
     * @param string       $entityClass The type of entity to retrieve.
     * @param array        $criteria    The criteria to filter by.
     * @param array        $orderBy     The properties to sort by.
     * @param array        $properties  The properties to hydrate.
     * @param integer|null $hydrator    The hydrator to use or null for the default hydrator
     *                                  (see Query::HYDRATE_* constants).
     * @param bool         $distinct    If the query should be distinct or not. (Default true)
     *
     * @throws \Exception When properties are specified and we're using the default hydrator or an object hydrator.
     *
     * @return Collection|array A collection of entities or an array depending on the hydrator.
     */
    public function getBy(
        string $entityClass,
        array $criteria,
        array $orderBy = array(),
        array $properties = array(),
        $hydrator = null,
        bool $distinct = true
    ) {
        // If properties are specified and we're using the default hydrator or an object hydrator, throw an exception.
        if (count($properties) > 0 and in_array($hydrator, array(null, Query::HYDRATE_OBJECT, Query::HYDRATE_SIMPLEOBJECT))) {
            throw new \Exception('Cannot specify properties when using object hydration');
        }
        // Create query builder for this type of entity.
        $qb = $this->entityManager->getRepository($entityClass)->createQueryBuilder('e');
        // Initialize an array to hold all necessary joins.
        $joins = array();
        // Process the properties.
        $this->processProperties($entityClass, $properties, $qb, $joins);
        // Process the critera.
        $this->processCriteria($criteria, $qb, $joins);
        // Process the order by.
        $this->processOrderBy($orderBy, $qb, $joins);
        // Join all necessary joins.
        foreach ($joins as $entityProperty => $alias) {
            $qb->leftJoin($entityProperty, $alias);
        }
        // Filter by Funding Organization is needed.
        $this->filterByFundingOrganization($qb, $entityClass);
        // Eliminate duplicates.
        $qb->distinct($distinct);
        // Get the query.
        $query = $qb->getQuery();
        // Return the result using the requested hydrator.
        return $query->getResult($hydrator);
    }

    private function filterByFundingOrganization(QueryBuilder $qb, string $entityClass)
    {
        if (!$this->fundingOrgFilter->isActive()) {
            return;
        }

        $researchGroupIds = $this->fundingOrgFilter->getResearchGroupsIdArray();

        switch ($entityClass) {
            case ResearchGroup::class:
                $qb->andWhere('e.id IN (:rgs)');
                $qb->setParameter('rgs', $researchGroupIds);
                break;
            case Dataset::class:
                $qb->innerJoin('e.researchGroup', 'rg');
                $qb->andWhere('rg.id IN (:rgs)');
                $qb->setParameter('rgs', $researchGroupIds);
                break;
        }
    }

    /**
     * Count all entities of $entityClass filtered by $criteria.
     *
     * @param string $entityClass The type of entity to count.
     * @param array  $criteria    The criteria to filter by.
     *
     * @return intger
     */
    public function count(string $entityClass, array $criteria)
    {
        // Create query builder for this type of entity.
        $qb = $this->entityManager->getRepository($entityClass)->createQueryBuilder('e');
        // Initialize an array to hold all necessary joins.
        $joins = array();
        // Select a distinct count of this type of entity.
        $qb->select($qb->expr()->countDistinct('e'));
        // Process the critera.
        $this->processCriteria($criteria, $qb, $joins);
        // Join all necessary joins.
        foreach ($joins as $entityProperty => $alias) {
            $qb->leftJoin($entityProperty, $alias);
        }
        // Get the query.
        $query = $qb->getQuery();
        // Return the result as a single scalar.
        return $query->getSingleScalarResult();
    }

    /**
     * Create a new entity.
     *
     * @param Entity      $entity          The entity to create.
     * @param string|null $entityEventName The name of the entity event to dispatch (default: 'created').
     *
     * @throws \Exception            When the entity is already tracked by the entity manager.
     * @throws AccessDeniedException When the user does not have sufficient privileges to create the entity.
     *
     * @return Entity The new entity.
     */
    public function create(Entity $entity, $entityEventName = 'created')
    {
        if ($this->entityManager->contains($entity)) {
            throw new \Exception('Attempted to create a ' . $entity::FRIENDLY_NAME . ' that is already tracked');
        }
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
        $this->entityManager->flush();
        // If the entity has been manually assigned an ID.
        if ($id !== null) {
            // Restore the original ID generator for entities of this class.
            $metadata->setIdGenerator($idGenerator);
        }
        $this->entityEventDispatcher->dispatch($entity, $entityEventName);
        return $entity;
    }

    /**
     * Update an entity.
     *
     * @param Entity      $entity          The entity to update.
     * @param string|null $entityEventName The name of the entity event to dispatch (default: 'updated').
     *
     * @throws \Exception            When the entity is not tracked by the entity manager.
     * @throws AccessDeniedException When the user does not have sufficient privileges to update the entity.
     *
     * @return Entity The updated entity.
     */
    public function update(Entity $entity, $entityEventName = 'updated')
    {
        if (!$this->entityManager->contains($entity)) {
            throw new \Exception('Attempted to update an untracked ' . $entity::FRIENDLY_NAME);
        }
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
        $this->entityManager->flush();
        $this->entityEventDispatcher->dispatch($entity, $entityEventName);
        return $entity;
    }

    /**
     * Delete an entity.
     *
     * @param Entity      $entity          The entity object to delete.
     * @param string|null $entityEventName The name of the entity event to dispatch (default: 'deleted').
     *
     * @throws \Exception            When the entity is not tracked by the entity manager.
     * @throws AccessDeniedException When the user does not have sufficient privileges to delete the entity.
     *
     * @return Entity The entity object that was deleted.
     */
    public function delete(Entity $entity, $entityEventName = 'deleted')
    {
        if (!$this->entityManager->contains($entity)) {
            throw new \Exception('Attempted to delete an untracked ' . $entity::FRIENDLY_NAME);
        }
        if (!$this->authorizationChecker->isGranted(PelagosEntityVoter::CAN_DELETE, $entity)) {
            throw new AccessDeniedException(
                'You do not have sufficient privileges to delete this ' . $entity::FRIENDLY_NAME . '.'
            );
        }
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        $this->entityEventDispatcher->dispatch($entity, $entityEventName);
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
    public function getDistinctVals(string $entityClass, string $property)
    {
        $entity = new $entityClass();
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException(
                'Only authenticated users may retrieve a list of distinct values ' .
                "for property $property of " . $entity::FRIENDLY_NAME . '.'
            );
        }
        $class = $this->entityManager->getClassMetadata($entityClass);
        if (!$class->hasField($property) && !$class->hasAssociation($property)) {
            throw new UnmappedPropertyException($entityClass, $property);
        }
        $this->entityManager
            ->getConfiguration()
            ->addCustomHydrationMode(
                'COLUMN_HYDRATOR',
                'App\DoctrineExtensions\Hydrators\ColumnHydrator'
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
     * Get the currently authenticated Person.
     *
     * @return Person The currently authenticated Person.
     */
    protected function getAuthenticatedPerson()
    {
        $user = $this->security->getUser();
        // If user is authenticated.
        if ($user instanceof Account) {
            // Return the authenticated person.
            return $user->getPerson();
        }
        // Return the anonymous person by default.
        return $this->get(Person::class, -1);
    }

    /**
     * Process filter criteria and add filters to a query builder.
     *
     * @param array        $criteria The criteria to process.
     * @param QueryBuilder $qb       A query builder to add to.
     * @param array        $joins    The joins array that is passed by reference and updated with new joins.
     *
     * @return void
     */
    protected function processCriteria(array $criteria, QueryBuilder $qb, array &$joins)
    {
        // Initialize our parameter tokens at 1.
        $paramToken = 1;
        // Loop through the criteria.
        foreach ($criteria as $property => $rawValue) {
            $orX = $qb->expr()->orX();
            $valueList = array($rawValue);
            if (gettype($rawValue) === 'string') {
                $valueList = explode(',', $rawValue);
            }
            // Get the alias and the property.
            list ($alias, $property) = $this->buildAliasedProperty($property, $joins);
            foreach ($valueList as $value) {
                if (gettype($value) === 'string') {
                    if ('*' === $value) {
                        $compareExpression = $qb->expr()->isNotNull("$alias.$property");
                    } elseif ('!*' === $value) {
                        $compareExpression = $qb->expr()->isNull("$alias.$property");
                    } else {
                        // The default compare is equivalency.
                        $compareExpression = $qb->expr()->eq("$alias.$property", "?$paramToken");
                        // If the criteria value contains unescaped wildcard characters (* or ?).
                        if (preg_match('/(?<!\\\\)(\*|\?)/', $value)) {
                            // Use like for comparison instead.
                            $compareExpression = $qb->expr()->like("$alias.$property", "?$paramToken");
                            // Escape %.
                            $value = preg_replace('/%/', '\\\\%', $value);
                            // Replace unescaped * with %.
                            $value = preg_replace('/(?<!\\\\)\*/', '%', $value);
                            // Unescape escaped *.
                            $value = preg_replace('/\\\\\*/', '*', $value);
                            // Escape _.
                            $value = preg_replace('/_/', '\\\\_', $value);
                            // Replace unescaped ? with _.
                            $value = preg_replace('/(?<!\\\\)\?/', '_', $value);
                            // Unescape escaped ?.
                            $value = preg_replace('/\\\\\?/', '?', $value);
                        }
                        if (preg_match('/^!/', $value)) {
                            $value = preg_replace('/^!/', '', $value);
                            $compareExpression = $qb->expr()->not($compareExpression);
                        }
                        // Set the parameter.
                        $qb->setParameter($paramToken, $value);
                    }
                } else {
                    $compareExpression = $qb->expr()->eq("$alias.$property", "?$paramToken");
                    $qb->setParameter($paramToken, $value);
                }
                $orX->add($compareExpression);
                // Increment our parameter token counter;
                $paramToken++;
            }
            // Filter by the aliased property.
            $qb->andWhere($orX);
        }
    }

    /**
     * Process order by criteria and add order by to a query builder.
     *
     * @param array        $orderBy The order by criteria to process.
     * @param QueryBuilder $qb      A query builder to add to.
     * @param array        $joins   The joins array that is passed by reference and updated with new joins.
     *
     * @return void
     */
    protected function processOrderBy(array $orderBy, QueryBuilder $qb, array &$joins)
    {
        // Loop through the properties to order by.
        foreach ($orderBy as $property => $order) {
            // Get the alias and the property.
            list ($alias, $property) = $this->buildAliasedProperty($property, $joins);
            // Order by the aliased property.
            $qb->addOrderBy("$alias.$property", $order);
        }
    }

    /**
     * Process the property specification.
     *
     * @param string       $entityClass The type of entity to process properties for.
     * @param array        $properties  The properties array to process.
     * @param QueryBuilder $qb          The query builder to add a select to.
     * @param array        $joins       The joins array that is passed by reference and updated with new joins.
     *
     * @throws \Exception When attempting to access a protected entity.
     *
     * @return void
     */
    protected function processProperties(string $entityClass, array $properties, QueryBuilder $qb, array &$joins)
    {
        // An array to hold all the entity aliases their properties to hydrate.
        $hydrate = array();
        // If the properties array is empty.
        if (count($properties) === 0) {
            // Just hydrate root entity alias with no properties.
            $hydrate['e'] = true;
        } else {
            foreach ($properties as $property) {
                // Get the entity alias and the property.
                list ($alias, $property) = $this->buildAliasedProperty($property, $joins);
                // Process the entity alias.
                $propertyMetadata = $this->processEntityAlias($entityClass, $alias, $hydrate, $joins);
                // If the property is an association.
                if (array_key_exists($property, $propertyMetadata->associationMappings)) {
                    // Get the target entitiy.
                    $targetEntity = $propertyMetadata->associationMappings[$property]['targetEntity'];
                    // If it's a protected entity, throw an exception.
                    if (in_array($targetEntity, $this->protectedEntities)) {
                        throw new \Exception("Access to $targetEntity not allowed");
                    }
                    // Join the property.
                    $joins["$alias.$property"] = $alias . '_' . $property;
                    // Hydrate the property.
                    $hydrate[$alias . '_' . $property] = true;
                    // If the property's parent is not the root and it's not already marked for hydration.
                    if ('e' !== $alias and !array_key_exists($alias, $hydrate)) {
                        // Hydrate its id.
                        $hydrate[$alias] = array('id');
                    }
                    // Nothing more to do for this property.
                    continue;
                }
                // If this entity alias is already marked for hydration.
                if (array_key_exists($alias, $hydrate)) {
                    // And it's a partial hydration.
                    if (is_array($hydrate[$alias])) {
                        // Add the property.
                        $hydrate[$alias][] = $property;
                    }
                } else {
                    // Mark this entity alias for partial hydration with this property.
                    $hydrate[$alias] = array($property);
                }
            }
        }
        // Add the selects to the query builder.
        $qb->select($this->buildSelect($hydrate));
    }

    /**
     * Process an entity alias and return the class metadata for the last node in the path.
     *
     * @param string $entityClass The type of entity to process an entity alias for.
     * @param string $alias       The alias to process.
     * @param array  $hydrate     The hydrate array that is passed by reference and updated with new joins.
     * @param array  $joins       The joins array that is passed by reference and updated with new joins.
     *
     * @throws \Exception When attempting to access a protected entity.
     *
     * @return ClassMetadata
     */
    protected function processEntityAlias(string $entityClass, string $alias, array &$hydrate, array &$joins)
    {
        // Split the entity alias on _ and loop through the nodes.
        foreach (explode('_', $alias) as $node) {
            // If we're on the root node.
            if ('e' === $node) {
                // Get the class metadata for the root entity type.
                $nodeMetadata = $this->entityManager->getClassMetadata($entityClass);
                // Set the parent to the root entity.
                $parent = 'e';
            } elseif (array_key_exists($node, $nodeMetadata->associationMappings)) {
                // Get the target entity.
                $targetEntity = $nodeMetadata->associationMappings[$node]['targetEntity'];
                 // If it's a protected entity, throw an exception.
                if (in_array($targetEntity, $this->protectedEntities)) {
                    throw new \Exception("Access to $targetEntity not allowed");
                }
                // Join this node.
                $joins["$parent.$node"] = $parent . '_' . $node;
                // If the parent is not the root entity and it's not already marked for hydration.
                if ('e' !== $parent and !array_key_exists($parent, $hydrate)) {
                    // Hydrate its id.
                    $hydrate[$parent] = array('id');
                }
                // Get the class metadata for the entityProperty's type.
                $nodeMetadata = $this->entityManager->getClassMetadata($targetEntity);
                // Append the current node to the parent string.
                $parent .= "_$node";
            }
        }
        // Return the last node's class metadata.
        return $nodeMetadata;
    }

    /**
     * Build an alias to the entity for a property.
     *
     * @param string $property The property to build aliases for.
     * @param array  $joins    The joins array that is passed by reference and updated with new joins.
     *
     * @return array The entity alias and the property.
     */
    protected function buildAliasedProperty(string $property, array &$joins)
    {
        // Initialize the alias to 'e', the root entity.
        $alias = 'e';
        // While the property contains a dot or an underscore.
        while (preg_match('/^([^\._]+)[\._](.+)$/', $property, $matches)) {
            // Extract the entity property and the remaining property.
            list (, $entityProperty, $property) = $matches;
            // Create a unique alias for this entity property.
            $entityPropertyAlias = str_replace('.', '_', "$alias.$entityProperty");
            // Join the entity property with the unique alias.
            $joins["$alias.$entityProperty"] = $entityPropertyAlias;
            // Update the alias to be the entity property alias.
            $alias = $entityPropertyAlias;
        }
        // Return the final alias and the last operty.
        return array($alias, $property);
    }

    /**
     * Build the select array for query builder.
     *
     * @param array $hydrate An array of entity aliases and their properties to hydrate.
     *
     * @return array
     */
    protected function buildSelect(array $hydrate)
    {
        // If the root entity alias is not already marked for hydration.
        if (!in_array('e', array_keys($hydrate))) {
            // Mark it for full hydration.
            $hydrate['e'] = true;
        }
        // Initialize an array to hold select DQL.
        $select = array();
        // Loop though our aliases to be hydrated.
        foreach ($hydrate as $alias => $properties) {
            // If properties is in an array.
            if (is_array($properties)) {
                // Make sure id is included.
                if (!in_array('id', $properties)) {
                    $properties[] = 'id';
                }
                // Do a partial hydration.
                $select[] = "partial $alias.{" . implode(',', $properties) . '}';
            } else {
                // Do a full hydration of this alias.
                $select[] = $alias;
            }
        }
        return $select;
    }
}
