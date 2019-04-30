<?php

namespace Pelagos\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Pelagos\Entity\DataRepository;
use Pelagos\Entity\Entity;
use Pelagos\Entity\Person;
use Pelagos\Entity\RoleInterface;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Fixture to load test People into the GRIIDC Data Repository.
 */
class BootstrapDRPMFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * A symfony container interface, required to implement ContainerAwareInterface.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * The entity manager to use in this data fixture.
     *
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * The system person to use in this data fixture.
     *
     * @var mixed
     */
    protected $systemPerson;

    /**
     * A method to pass in the container, required to implement ContainerAwareInterface.
     *
     * @param ContainerInterface $container The container passed in.
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load a parameter-designated person into Pelagos and make them a DRPM.
     *
     * @param ObjectManager $entityManager The entity manager to use.
     *
     * @throws \Exception When the Data Repository Role: Manager is not found.
     *
     * @return void
     */
    public function load(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;

        $this->systemPerson = $entityManager->find(Person::class, 0);

        // make container aware
        if ($this->container->hasParameter('super_drpm_first_name')) {
            $superDrpmFirstName = $this->container->getParameter('super_drpm_first_name');
        }
        if ($this->container->hasParameter('super_drpm_last_name')) {
            $superDrpmLastName = $this->container->getParameter('super_drpm_last_name');
        }
        if ($this->container->hasParameter('super_drpm_email')) {
            $superDrpmEmail = $this->container->getParameter('super_drpm_email');
        }

        // only if parameters above are all set
        if (isset($superDrpmFirstName)
            and isset($superDrpmLastName)
            and isset($superDrpmEmail)) {
            $person = new Person;

            $person->setFirstName($superDrpmFirstName);
            $person->setLastName($superDrpmLastName);
            $person->setEmailAddress($superDrpmEmail);
            $person->setCreator($this->systemPerson);

            // Bestow superpowers.
            $this->givePersonRole(
                $person,
                'DataRepository',
                DataRepositoryRoles::MANAGER,
                $this->entityManager->find(DataRepository::class, 1)
            );

            $entityManager->persist($person);
            $entityManager->flush();
        }
    }

    /**
     * The order this fixture should be run in.
     *
     * @return integer
     */
    public function getOrder()
    {
        return 20;
    }

    /**
     * Give a Person a role.
     *
     * @param Person $person     The Person to give a role to.
     * @param string $targetType The type of the the role target (e.g. DataRepository).
     * @param string $roleName   The name of the role.
     * @param Entity $target     The target entity of the role.
     *
     * @throws \Exception When $roleName is not found as a valid role.
     *
     * @return void
     */
    protected function givePersonRole(
        Person $person,
        $targetType,
        $roleName,
        Entity $target
    ) {
        $role = $this->entityManager
                     ->getRepository('Pelagos:' . $targetType . 'Role')
                     ->findOneByName($roleName);
        if (!$role instanceof RoleInterface) {
            throw new \Exception('Could not find ' . $targetType . ' Role: ' . $roleName);
        }
        $personAssociationClass = '\Pelagos\Entity\Person' . $targetType;
        $personAssociation = new $personAssociationClass;
        $personAssociation->setPerson($person);
        $personAssociation->setRole($role);
        $personAssociation->setLabel('Test ' . $role->getName());
        $personAssociation->setCreator($this->systemPerson);
        $targetSetter = 'set' . $targetType;
        $personAssociation->$targetSetter($target);
        $this->entityManager->persist($personAssociation);
    }
}
