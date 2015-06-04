<?php

namespace Pelagos;

/**
 * Unit tests for Pelagos\Component.
 *
 * @group Pelagos
 * @group Pelagos\Component
 */
class ComponentTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Pelagos\Component $component Class variable to hold an instance of \Pelagos\Component to test against */
    protected $component;

    /**
     * Set up method.
     * Alias mock \Pelagos\Persistance::createEntityManager() to return a mock \Doctrine\ORM\EntityManager
     * Create an instance of \Pelagos\Component and save it in $this->component
     */
    public function setUp()
    {
        \Mockery::mock(
            'alias:\Pelagos\Persistance',
            array(
                'createEntityManager' => \Mockery::mock(
                    '\Doctrine\ORM\EntityManager'
                ),
            )
        );
        $this->component = new \Pelagos\Component;
    }

    /**
     * Test retrieving the entity manager from the component.
     * Checks to see that we get an instance of \Doctrine\ORM\EntityManager back.
     */
    public function testGetEntityManager()
    {
        $entityManager = $this->component->getEntityManager();
        $this->assertInstanceOf('\Doctrine\ORM\EntityManager', $entityManager);
    }
}
