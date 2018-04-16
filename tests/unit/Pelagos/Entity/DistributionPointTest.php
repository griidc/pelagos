<?php

namespace Pelagos\Entity;

/**
 * Unit tests for Pelagos\Entity\Distributionpoint.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\DistributionPoint
 */
class DistributionPointTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instance of distributionPoint for testing.
     *
     * @var DistributionPoint $distributionPoint
     */
    protected $distributionPoint;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of DistributionPoint.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->mockDatasetSubmission = \Mockery::mock('\Pelagos\Entity\DatasetSubmission');
        $this->mockNationalDataCenter = \Mockery::mock('\Pelagos\Entity\NationalDataCenter');
        $this->distributionPoint = new DistributionPoint;

    }

    /**
     * Test the Dataset Submission setter and getter method.
     *
     * This method should return the DatasetSubmission.
     *
     * @return void
     */
    public function testCanSetAndGetDatasetSubmission()
    {
        $this->distributionPoint->setDatasetSubmission($this->mockDatasetSubmission);
        $this->assertSame(
            $this->mockDatasetSubmission,
            $this->distributionPoint->getDatasetSubmission()
        );
    }

    /**
     * Test the National Data Center setter and getter method.
     *
     * This method should return the NationalDataCenter.
     *
     * @return void
     */
    public function testCanSetAndGetNationalDataCenter()
    {
        $this->distributionPoint->setNationalDataCenter($this->mockNationalDataCenter);
        $this->assertSame(
            $this->mockNationalDataCenter,
            $this->distributionPoint->getNationalDataCenter()
        );
    }

    /**
     * Test the Distribution Url setter and getter method.
     *
     * This method should return the Distribution Url string.
     *
     * @return void
     */
    public function testCanSetAndGetDistributionUrl()
    {
        $mockDistributionUrl = 'www.1234.com';
        $this->distributionPoint->setDistributionUrl($mockDistributionUrl);
        $this->assertEquals(
            $mockDistributionUrl,
            $this->distributionPoint->getDistributionUrl()
        );
    }
}
