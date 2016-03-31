<?php

namespace Pelagos\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Doctrine\ORM\Id\AssignedGenerator;

use Pelagos\Entity\Person;

/**
 * Fixture to load system people.
 */
class SystemPeople extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load system people.
     *
     * @param ObjectManager $entityManager The entity manager to use.
     *
     * @return void
     */
    public function load(ObjectManager $entityManager)
    {
        $person = new Person;
        $person->setId(0);
        $person->setFirstName('System');
        $person->setLastName('User');
        $person->setEmailAddress('pelagos@griidc.org');
        $person->setCreator($person);
        $anonymousPerson = new Person;
        $anonymousPerson->setID(-1);
        $anonymousPerson->setFirstName('Anonymous');
        $anonymousPerson->setLastName('User');
        $anonymousPerson->setEmailAddress('anonymous@griidc.org');
        $anonymousPerson->setCreator($person);
        $metadata = $entityManager->getClassMetaData(Person::class);
        $idGenerator = $metadata->idGenerator;
        $metadata->setIdGenerator(new AssignedGenerator());
        $entityManager->persist($person);
        $entityManager->persist($anonymousPerson);
        $entityManager->flush();
        $metadata->setIdGenerator($idGenerator);
    }

    /**
     * The order this fixture should be run in.
     *
     * @return integer
     */
    public function getOrder()
    {
        return 0;
    }
}
