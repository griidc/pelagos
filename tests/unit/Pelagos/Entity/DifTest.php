<?php

namespace Pelagos\Entity;

use PHPUnit\Framework\TestCase;

class DifTest extends TestCase
{

    /**
     * @var DIF
     */
    protected $dif;

    /**
     * @var ResearchGroup
     */
    protected $mockResearchGroup;

    /**
     * Setup for all the test cases in National Data center entity test.
     *
     * @return void
     */
    public function setUp()
    {
        $this->dif = new DIF();
        $this->mockResearchGroup = \Mockery::mock(ResearchGroup::class,
            array(
                'setName' => 'Test Research Group',
            ));
    }

    /**
     * Test getter and setter for dataset.
     *
     * @return void
     */
    public function testInstanceOfDif()
    {
        $this->assertInstanceOf(DIF::class, $this->dif);

    }

    public function testCanGetAndSetDataset()
    {
        $dataset = new Dataset();

        $this->dif->setDataset($dataset);

        $this->assertEquals($dataset, $this->dif->getDataset());
    }

    /**
     * Test getter and setter for status.
     * 
     * @return void
     */
    public function testCanGetAndSetStatus()
    {
        try {
            $difReflection = new \ReflectionClass(DIF::class);
        } catch (\ReflectionException $reflectionException) {
            echo 'Unable to create reflection class for DIF';
        }

        $setStatus = $difReflection->getProperty('status');
        $setStatus->setAccessible(true);
        $setStatus->setValue($this->dif,DIF::STATUS_UNSUBMITTED);

        $this->assertEquals( DIF::STATUS_UNSUBMITTED, $this->dif->getStatus());
    }

    /**
     * Test getter and setter for research group.
     *
     * @return void
     */
    public function testCanGetAndSetResearchGroup()
    {
        $this->dif->setDataset(new Dataset);
        $this->dif->setResearchGroup($this->mockResearchGroup);
        $this->assertEquals($this->mockResearchGroup, $this->dif->getResearchGroup());
    }

    /**
     * Test getter and setter for title.
     *
     * @return void
     */
    public function testCanGetAndSetTitle()
    {
        $title = 'Random DIF Title for Unit testing';
        $this->dif->setTitle($title);
        $this->assertEquals($title, $this->dif->getTitle());
    }

    /**
     * Test getter and setter for primary point of contact.
     * 
     * @return void
     */
    public function testCanGetAndSetPrimaryPointOfContact()
    {
        $primaryContact = new Person;
        $this->dif->setPrimaryPointOfContact($primaryContact);
        $this->assertEquals($primaryContact, $this->dif->getPrimaryPointOfContact());
    }

    /**
     * Test getter and setter for primary point of contact.
     *
     * @return void
     */
    public function testCanGetAndSetSecondaryPointOfContact()
    {
        $secondaryContact = new Person;
        $this->dif->setSecondaryPointOfContact($secondaryContact);
        $this->assertEquals($secondaryContact, $this->dif->getSecondaryPointOfContact());
    }

    /**
     * Test getter and setter for abstract.
     *
     * @return void
     */
    public function testCanGetAndSetAbstract()
    {
        $abstract = 'This is the abstract for the DIF';
        $this->dif->setAbstract($abstract);
        $this->assertEquals($abstract, $this->dif->getAbstract());
    }
}