<?php

namespace Tests\unit\Pelagos\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Web Test for Pelagos.
 */
class MainPagesTest extends WebTestCase
{
    /**
     * Property to hold an instance of Account for testing.
     */
    private $client = null;

     /**
     * Setup for PHPUnit tests.
     *
     * @return void
     */
    public function setUp()
    {
        $this->client = static::createClient();
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testCommonPages()
    {
        $routes = array(
            'pelagos_homepage',
            'pelagos_app_ui_datadiscovery_default',
            'pelagos_admin',
            'security_login',
            'pelagos_app_ui_datasetmonitoring_default',
            'pelagos_app_ui_stats_default',
        );

        foreach ($routes as $route) {
            $url = $this->client->getContainer()->get('router')->generate($route, array(), false);
            $crawler = $this->client->request('GET', $url);
            $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        }
    }
}