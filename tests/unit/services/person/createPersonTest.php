<?php

namespace Pelagos;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . "/../../../../web/services/person/lib/PersonService.php";

class testUser
{
    public $name = 'test';
}


class servicePersonTest extends \PHPUnit_Framework_TestCase
{
    protected $personService;
    protected $first = 'test';
    protected $last = 'user';
    protected $email;
    protected $guid = null;

    protected function setUp()
    {

        $this->personService = new \Pelagos\Component\PersonService();
        $GLOBALS['user'] = new testUser;
        // create a unique string as an ID
        $guid = uniqid();
    }


    // test cases
    public function testCreateNormalPerson()
    {
        $first = "test";
        $last = "user";
        $email = "test.user-".$this->guid."@testdomain.tld";

        // this person should be created, and a code 200 returned.
        $entityManager = $this
            ->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->personService->createPerson($entityManager, 'test', 'user', $email);
        $response = $this->personService->slim->response->getBody();

        $this->assertRegExp('/{"code":200/', $response);
    }

    public function testCreatePersonWithBadEmail()
    {
        $first = "test";
        $last = "user";
        $email = "test.user-".$this->guid."@testdomain@tld;";

        // this person should be created, and a code 200 returned.
        $entityManager = $this
            ->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->personService->createPerson($entityManager, 'test', 'user', $email);
        $response = $this->personService->slim->response->getBody();

        $this->assertRegExp('/{"code":400/', $response);

    }

}
