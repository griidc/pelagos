<?php

namespace App\Tests\Entity;

use App\Entity\Dataset;
use App\Entity\DIF;
use App\Entity\Person;
use App\Entity\ResearchGroup;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\DIF.
 */
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
     * Setup for all the test cases in DIF entity test.
     *
     * @return void
     */
    public function setUp(): void
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
     * Test getter and setter for secondary point of contact.
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

    /**
     * Test getters and setters for field of study.
     *
     * @return void
     */
    public function testCanGetAndSetFieldOfStudy()
    {
        $bool = true;
        $this->dif->setFieldOfStudyAtmospheric($bool);
        $this->dif->setFieldOfStudyChemical($bool);
        $this->dif->setFieldOfStudyEcologicalBiological($bool);
        $this->dif->setFieldOfStudyEconomics($bool);
        $this->dif->setFieldOfStudyHumanHealth($bool);
        $this->dif->setFieldOfStudyOther($bool);
        $this->dif->setFieldOfStudyPhysicalOceanography($bool);
        $this->dif->setFieldOfStudySocialCulturalPolitical($bool);

        $this->assertEquals($bool, $this->dif->getFieldOfStudyOther());
        $this->assertEquals($bool, $this->dif->hasFieldOfStudyAtmospheric());
        $this->assertEquals($bool, $this->dif->hasFieldOfStudyChemical());
        $this->assertEquals($bool, $this->dif->hasFieldOfStudyEcologicalBiological());
        $this->assertEquals($bool, $this->dif->hasFieldOfStudyEconomics());
        $this->assertEquals($bool, $this->dif->hasFieldOfStudySocialCulturalPolitical());
        $this->assertEquals($bool, $this->dif->hasFieldOfStudyPhysicalOceanography());
        $this->assertEquals($bool, $this->dif->hasFieldOfStudyHumanHealth());
        $this->assertEquals($bool, $this->dif->hasFieldOfStudyAtmospheric());
    }

    /**
     * Test getter and setter for data size.
     *
     * @return void
     */
    public function testCanGetAndSetDataSize()
    {
        $this->dif->setDataSize(DIF::DATA_SIZES[0]);
        $this->assertEquals(DIF::DATA_SIZES[0], $this->dif->getDataSize());
    }

    /**
     * Test getter and setter for variables observed.
     *
     * @return void
     */
    public function testCanGetAndSetVariablesObserved()
    {
        $this->dif->setVariablesObserved('variables observed');
        $this->assertEquals('variables observed', $this->dif->getVariablesObserved());
    }

    /**
     * Test getter and setter for collection method.
     *
     * @return void
     */
    public function testCanGetAndSetCollectionMethodFieldSampling()
    {
        $this->dif->setCollectionMethodFieldSampling(true);
        $this->assertEquals(true, $this->dif->hasCollectionMethodFieldSampling());

        $this->dif->setCollectionMethodSimulatedGenerated(true);
        $this->assertEquals(true, $this->dif->hasCollectionMethodSimulatedGenerated());

        $this->dif->setCollectionMethodLaboratory(true);
        $this->assertEquals(true, $this->dif->hasCollectionMethodLaboratory());

        $this->dif->setCollectionMethodLiteratureBased(true);
        $this->assertEquals(true, $this->dif->hasCollectionMethodLiteratureBased());

        $this->dif->setCollectionMethodRemoteSensing(true);
        $this->assertEquals(true, $this->dif->hasCollectionMethodRemoteSensing());

        $this->dif->setCollectionMethodOther('other');
        $this->assertEquals('other', $this->dif->getCollectionMethodOther());
    }

    /**
     * Test getter and setter for estimated start date.
     *
     * @return void
     */
    public function testCanGetAndSetEstimatedStartDate()
    {
        $dateTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->dif->setEstimatedStartDate($dateTime);
        $this->assertEquals($dateTime, $this->dif->getEstimatedStartDate());
    }

    /**
     * Test getter and setter for estimated end date.
     *
     * @return void
     */
    public function testCanGetAndSetEstimatedEndDate()
    {
        $dateTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->dif->setEstimatedEndDate($dateTime);
        $this->assertEquals($dateTime, $this->dif->getEstimatedEndDate());
    }

    /**
     * Test getter and setter for spatial extent description.
     *
     * @return void
     */
    public function testCanGetAndSetSpatialExtentDescription()
    {
        $spatialExtentDesc = 'non-spatial';
        $this->dif->setSpatialExtentDescription($spatialExtentDesc);
        $this->assertEquals($spatialExtentDesc, $this->dif->getSpatialExtentDescription());
    }

    /**
     * Test getter and setter for spatial extent geometry.
     *
     * @return void
     */
    public function testCanGetAndSetSpatialExtentGeometry()
    {
        $spatialExtentGeometry = '00.00,-00.00 00.00';
        $this->dif->setSpatialExtentGeometry($spatialExtentGeometry);
        $this->assertEquals($spatialExtentGeometry, $this->dif->getSpatialExtentGeometry());
    }

    /**
     * Test getter and setter for national archive.
     *
     * @return void
     */
    public function testCanGetAndSetNationalArchive()
    {
        $this->dif->setNationalDataArchiveNODC(true);
        $this->assertEquals(true, $this->dif->hasNationalDataArchiveNODC());

        $this->dif->setNationalDataArchiveStoret(true);
        $this->assertEquals(true, $this->dif->hasNationalDataArchiveStoret());

        $this->dif->setNationalDataArchiveGBIF(true);
        $this->assertEquals(true, $this->dif->hasNationalDataArchiveGBIF());

        $this->dif->setNationalDataArchiveNCBI(true);
        $this->assertEquals(true, $this->dif->hasNationalDataArchiveNCBI());

        $this->dif->setNationalDataArchiveDataGov(true);
        $this->assertEquals(true, $this->dif->hasNationalDataArchiveDataGov());

        $this->dif->setNationalDataArchiveOther(true);
        $this->assertEquals(true, $this->dif->getNationalDataArchiveOther());
    }

    /**
     * Test getter and setter for ethical issues.
     *
     * @return void
     */
    public function testCanGetAndSetEthicalIssues()
    {
        $this->dif->setEthicalIssues(DIF::ETHICAL_ISSUES[1]);
        $this->assertEquals(DIF::ETHICAL_ISSUES[1], $this->dif->getEthicalIssues());
    }

    /**
     * Test getter and setter for ethical issues explanation.
     *
     * @return void
     */
    public function testCanGetAndSetEhicalIssuesExplanation()
    {
        $this->dif->setEthicalIssuesExplanation('test');
        $this->assertEquals('test', $this->dif->getEthicalIssuesExplanation());
    }

    /**
     * Test getter and setter for remarks.
     *
     * @return void
     */
    public function testCanGetAndSetRemarks()
    {
        $this->dif->setRemarks('remarks');
        $this->assertEquals('remarks', $this->dif->getRemarks());
    }

    /**
     * Test getter and setter for approved date.
     *
     * @return void
     */
    public function testCanGetAndSetApprovedDate()
    {
        $approvedDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->dif->setApprovedDate($approvedDate);
        $this->assertEquals($approvedDate, $this->dif->getApprovedDate());

    }
}