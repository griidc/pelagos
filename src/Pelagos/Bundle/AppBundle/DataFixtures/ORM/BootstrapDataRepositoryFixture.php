<?php

namespace Pelagos\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pelagos\Entity\Person;
use Pelagos\Entity\DataRepository;

/**
 * Fixture to load a test organizational tree.
 */
class BootstrapDataRepositoryFixture extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load a test organizational tree.
     *
     * @param ObjectManager $entityManager The entity manager to use.
     *
     * @return void
     */
    public function load(ObjectManager $entityManager)
    {
        $systemPerson = $entityManager->find(Person::class, 0);

        $name = 'Initial Data Repository';
        $description = 'This data repository was created at installation time based ' .
            'on supplied parameters. A Person was also created who has DRPM-level access ' .
            'to this data repository. You can use this person to designate DRPM rights to ' .
            'other users. You may also edit this created data repository as needed.';
        $dataRepository = new DataRepository;
        $dataRepository->setName($name);
        $dataRepository->setEmailAddress('emailaddress@entity.tld');
        $dataRepository->setDescription($description);
        $dataRepository->setCreator($systemPerson);
        $dataRepository->setUrl('http://example-repository.tld');
        $dataRepository->setPhoneNumber('3615551212');
        $dataRepository->setDeliveryPoint('123 Any Street');
        $dataRepository->setCity('Cityville');
        $dataRepository->setAdministrativeArea('TX');
        $dataRepository->setPostalCode('00000');
        $dataRepository->setCountry('USA');

        $entityManager->persist($dataRepository);
        $entityManager->flush();
        $this->addReference($name, $dataRepository);
    }

    /**
     * The order this fixture should be run in.
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}
