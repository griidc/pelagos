<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Doctrine\ORM\EntityManager;

use Symfony\Component\HttpFoundation\Request;

use Pelagos\Entity\Logins;
use Pelagos\Entity\Person;

class LoginAttemptHandler
{
    /**
     * An instance of a Doctrine EntityManager class.
     *
     * @var EntityManager
     */
    private $entityManager;
    
     /**
     * Class constructor for Dependency Injection.
     *
     * @param EntityManager        $entityManager An Entity Manager.
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function log(Request $request)
    {
        $ipAddress = $request->getClientIp();
        $userName = $request->request->get('_username');
        $anonymousPerson = $this->entityManager->find(Person::class, -1);
        
        $login = new Logins($userName, $ipAddress);
        $login->setCreator($anonymousPerson);
        $this->entityManager->persist($login);
        $this->entityManager->flush($login);
    }
}