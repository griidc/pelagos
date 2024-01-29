<?php

namespace App\Tests\Entity;

use App\Entity\NationalDataCenter;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\DataCenter.
 */
class DataCenterTest extends TestCase
{
    /**
     * A mock name for the National data center.
     *
     * @var string
     */
    protected $organizationName;

    /**
     * A mock website URL for the National data center.
     *
     * @var string
     */
    protected $organizationUrl;

    /**
     * The national data center entity being tested.
     *
     * @var NationalDataCenter
     */
    protected $dataCenter;

    /**
     * Setup for all the test cases in National Data center entity test.
     *
     * @return void
     */
    public function setUp()
    {
        $this->dataCenter = new NationalDataCenter();
    }

    /**
     * Test created object is an instance of National data center entity.
     *
     * @return void
     */
    public function testInstanceOfNationalDataCenter()
    {
        $this->assertInstanceOf(NationalDataCenter::class, $this->dataCenter);
    }

    /**
     * Test to get the organization name.
     *
     * @return void
     */
    public function testCanSetAndGetOrganizationName()
    {
        $mockOrgName = 'GRIIDC';

        $this->dataCenter->setOrganizationName($mockOrgName);

        $this->assertEquals($mockOrgName, $this->dataCenter->getOrganizationName());
    }

    /**
     * Test to get the website URL for the organization.
     *
     * @return void
     */
    public function testCanSetAndGetOrganizationUrl()
    {
        $mockOrgUrl = 'griidc.org';

        $this->dataCenter->setOrganizationUrl($mockOrgUrl);

        $this->assertEquals($mockOrgUrl, $this->dataCenter->getOrganizationUrl());
    }

    /**
     * Test to set and get phone number for the organization.
     *
     * @return void
     */
    public function testCanSetAndGetPhoneNumber()
    {
        $mockPhoneNumber = '12345678990';

        $this->dataCenter->setPhoneNumber($mockPhoneNumber);

        $this->assertEquals($mockPhoneNumber, $this->dataCenter->getPhoneNumber());
    }

    /**
     * Test to set and get delivery point(street address) for the NDC.
     *
     * @return void
     */
    public function testCanSetAndGetDeliveryPoint()
    {
        $mockDeliveryPoint = '6300 Ocean Dr';

        $this->dataCenter->setDeliveryPoint($mockDeliveryPoint);

        $this->assertEquals($mockDeliveryPoint, $this->dataCenter->getDeliveryPoint());
    }

    /**
     * Test to set and get city for the NDC.
     *
     * @return void
     */
    public function testCanSetAndGetCity()
    {
        $mockCity = 'Corpus Christi';

        $this->dataCenter->setCity($mockCity);

        $this->assertEquals($mockCity, $this->dataCenter->getCity());
    }

    /**
     * Test to set and get administrative area(state) for the NDC.
     *
     * @return void
     */
    public function testCanSetAndGetAdministrativeArea()
    {
        $mockAdministrativeArea = 'Texas';

        $this->dataCenter->setAdministrativeArea($mockAdministrativeArea);

        $this->assertEquals($mockAdministrativeArea, $this->dataCenter->getAdministrativeArea());
    }

    /**
     * Test to set and get postal code (zip) for the NDC.
     *
     * @return void
     */
    public function testCanSetAndGetPostalCode()
    {
        $mockPostalCode = '00000';

        $this->dataCenter->setPostalCode($mockPostalCode);

        $this->assertEquals($mockPostalCode, $this->dataCenter->getPostalCode());
    }

    /**
     * Test to set and get country for the NDC.
     *
     * @return void
     */
    public function testCanSetAndGetCountry()
    {
        $mockCountry = 'United States';

        $this->dataCenter->setCountry($mockCountry);

        $this->assertEquals($mockCountry, $this->dataCenter->getCountry());
    }

    /**
     * Test to set and get email address for the NDC.
     *
     * @return void
     */
    public function testCanSetAndGetEmail()
    {
        $mockEmailAddress = 'help@griidc.org';

        $this->dataCenter->setEmailAddress($mockEmailAddress);

        $this->assertEquals($mockEmailAddress, $this->dataCenter->getEmailAddress());
    }
}
