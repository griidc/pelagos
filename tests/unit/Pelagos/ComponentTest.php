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
     * Creates an instance of \Pelagos\Component and saves it in $this->component
     */
    public function setUp()
    {
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
