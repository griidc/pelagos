<?php

namespace Tests\unit\Pelagos\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

use Symfony\Component\Security\Core\User\UserInterface;

use Pelagos\Entity\Account;
use Pelagos\Entity\Password;

/**
 * Web Test for Pelagos.
 */
class SecuredPageTest extends WebTestCase
{
    /**
     * Property to hold an instance of Account for testing.
     */
    private $client = null;
    
    /**
     * Property to hold an instance of Account for testing.
     */
    protected $account;

     /**
     * Setup for PHPUnit tests.
     *
     * @return void
     */
    public function setUp()
    {
        $this->client = static::createClient();
        
        $this->mockPerson = \Mockery::mock(
            '\Pelagos\Entity\Person',
            array(
                'setAccount' => null,
                'getAccount' => null
            )
        );
        $this->mockPassword = \Mockery::mock(
            '\Pelagos\Entity\Password',
            array(
                'setAccount' => null,
                'getPasswordHash' => null,
                'getClearTextPassword' => null
            )
        );
        $this->account = new Account($this->mockPerson, 'testuser', $this->mockPassword);
    }
    
    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewallName = 'main';
        $firewallContext = 'main';
        
        $token = new PostAuthenticationGuardToken($this->account, $firewallName, array('ROLE_DATA_REPOSITORY_MANAGER'));
        $token->setAuthenticated(true);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testDif()
    {
        $this->logIn();
        
        $url = $this->client->getContainer()->get('router')->generate('pelagos_admin', array(), false);
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("This page is only accessible by Data Repository Managers")')->count()
        );
    }
}