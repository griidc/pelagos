<?php

namespace Pelagos\Tests\Unit;

class PersonServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $personService;
    protected $firstName = 'test';
    protected $lastName = 'user';
    protected $emailAddress;
    protected $guid = null;

    protected function setUp()
    {
        require_once __DIR__ . '/../../../../vendor/autoload.php';
        require_once __DIR__ . "/../../../../web/services/person/lib/PersonService.php";

        $this->personService = new \Pelagos\Component\PersonService();
        $GLOBALS['user'] = new \Pelagos\TestUser;
        // create a unique string as an ID
        $guid = uniqid();
    }


    // test cases
    public function testCreateNormalPerson()
    {
        $firstName = "test";
        $lastName = "user";
        $emailAddress = "$firstName.$lastName-".$this->guid."@testdomain.tld";

        // this person should be created, and a code 200 returned.
        $entityManager = $this
            ->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->personService->createPerson($entityManager, $firstName, $lastName, $emailAddress);
        $response = $this->personService->slim->response->getBody();

        $this->assertRegExp('/{"code":200/', $response);
    }

    public function testCreatePersonWithBadEmail()
    {
        $firstName = "test";
        $lastName = "user";
        $emailAddress = "$firstName.$lastName-".$this->guid."@testdomain@tld";

        // this person should be created, and a code 200 returned.
        $entityManager = $this
            ->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->personService->createPerson($entityManager, $firstName, $lastName, $emailAddress);
        $response = $this->personService->slim->response->getBody();

        $this->assertRegExp('/{"code":400/', $response);

    }

    // this case represents a missing mandatory field, sent in as null
    public function testCreatePersonWithMissingEmail()
    {
        $firstName = "test";
        $lastName = "user";
        $emailAddress = null;

        // this person should be created, and a code 200 returned.
        $entityManager = $this
            ->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->personService->createPerson($entityManager, $firstName, $lastName, $emailAddress);
        $response = $this->personService->slim->response->getBody();

        $this->assertRegExp('/{"code":400/', $response);

    }

    // this case represents a missing mandatory field, sent in as empty string
    public function testCreatePersonWithEmptyFirstName()
    {
        $firstName = '';
        $lastName = "user";
        $emailAddress = "$firstName.$lastName-".$this->guid."@testdomain@tld";

        // this person should be created, and a code 200 returned.
        $entityManager = $this
            ->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->personService->createPerson($entityManager, $firstName, $lastName, $emailAddress);
        $response = $this->personService->slim->response->getBody();

        $this->assertRegExp('/{"code":400/', $response);

    }
}
