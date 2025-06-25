<?php

namespace App\Tests\Entity;

use App\Entity\DistributionPoint;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\Distributionpoint.
 */
class DistributionPointTest extends TestCase
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
    protected function setUp(): void
    {
        $this->mockDatasetSubmission = \Mockery::mock('\App\Entity\DatasetSubmission');
        $this->mockDataCenter = \Mockery::mock('\App\Entity\DataCenter');
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
     * Test the Data Center setter and getter method.
     *
     * This method should return the DataCenter.
     *
     * @return void
     */
    public function testCanSetAndGetDataCenter()
    {
        $this->distributionPoint->setDataCenter($this->mockDataCenter);
        $this->assertSame(
            $this->mockDataCenter,
            $this->distributionPoint->getDataCenter()
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

    /**
     * Test the Distribution Url Protocol getter method.
     *
     * This method should test the Distribution Url's protocol string.
     *
     * @return void
     */
    public function testCanGetDistributionUrlProtocol()
    {
        $mockDistributionUrlProtocol = 'ftps';
        $mockDistributionUrl = "$mockDistributionUrlProtocol://hostname.site.tld";
        $this->distributionPoint->setDistributionUrl($mockDistributionUrl);
        $this->assertEquals(
            $mockDistributionUrlProtocol,
            $this->distributionPoint->getDistributionUrlProtocol()
        );
        // Also try it if URL is null.
        $this->distributionPoint->setDistributionUrl(null);
        $this->assertNull($this->distributionPoint->getDistributionUrlProtocol());
    }

    /**
     * Test the role code setter and getter method.
     *
     * This method should return the role code string.
     *
     * @return void
     */
    public function testCanSetAndGetRoleCode()
    {
        $mockRoleCode = 'distributor';
        $this->distributionPoint->setRoleCode($mockRoleCode);
        $this->assertEquals(
            $mockRoleCode,
            $this->distributionPoint->getRoleCode()
        );
    }
}
