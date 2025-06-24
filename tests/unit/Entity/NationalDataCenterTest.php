<?php

namespace App\Tests\Entity;

use App\Entity\NationalDataCenter;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\NationalDataCenter.
 */
class NationalDataCenterTest extends TestCase
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
    protected $nationalDataCenter;

    /**
     * Setup for all the test cases in National Data center entity test.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->nationalDataCenter = new NationalDataCenter();
    }

    /**
     * Test created object is an instance of National data center entity.
     *
     * @return void
     */
    public function testInstanceOfNationalDataCenter()
    {
        $this->assertInstanceOf(NationalDataCenter::class, $this->nationalDataCenter);
    }

    /**
     * Test to get the organization name.
     *
     * @return void
     */
    public function testCanSetAndGetOrganizationName()
    {
        $mockOrgName = 'GRIIDC';

        $this->nationalDataCenter->setOrganizationName($mockOrgName);

        $this->assertEquals($mockOrgName, $this->nationalDataCenter->getOrganizationName());
    }

    /**
     * Test to get the website URL for the organization.
     *
     * @return void
     */
    public function testCanSetAndGetOrganizationUrl()
    {
        $mockOrgUrl = 'griidc.org';

        $this->nationalDataCenter->setOrganizationUrl($mockOrgUrl);

        $this->assertEquals($mockOrgUrl, $this->nationalDataCenter->getOrganizationUrl());
    }

    /**
     * Test to set and get phone number for the organization.
     *
     * @return void
     */
    public function testCanSetAndGetPhoneNumber()
    {
        $mockPhoneNumber = '12345678990';

        $this->nationalDataCenter->setPhoneNumber($mockPhoneNumber);

        $this->assertEquals($mockPhoneNumber, $this->nationalDataCenter->getPhoneNumber());
    }

    /**
     * Test to set and get delivery point(street address) for the NDC.
     *
     * @return void
     */
    public function testCanSetAndGetDeliveryPoint()
    {
        $mockDeliveryPoint = '6300 Ocean Dr';

        $this->nationalDataCenter->setDeliveryPoint($mockDeliveryPoint);

        $this->assertEquals($mockDeliveryPoint, $this->nationalDataCenter->getDeliveryPoint());
    }

    /**
     * Test to set and get city for the NDC.
     *
     * @return void
     */
    public function testCanSetAndGetCity()
    {
        $mockCity = 'Corpus Christi';

        $this->nationalDataCenter->setCity($mockCity);

        $this->assertEquals($mockCity, $this->nationalDataCenter->getCity());
    }

    /**
     * Test to set and get administrative area(state) for the NDC.
     *
     * @return void
     */
    public function testCanSetAndGetAdministrativeArea()
    {
        $mockAdministrativeArea = 'Texas';

        $this->nationalDataCenter->setAdministrativeArea($mockAdministrativeArea);

        $this->assertEquals($mockAdministrativeArea, $this->nationalDataCenter->getAdministrativeArea());
    }

    /**
     * Test to set and get postal code (zip) for the NDC.
     *
     * @return void
     */
    public function testCanSetAndGetPostalCode()
    {
        $mockPostalCode = '00000';

        $this->nationalDataCenter->setPostalCode($mockPostalCode);

        $this->assertEquals($mockPostalCode, $this->nationalDataCenter->getPostalCode());
    }

    /**
     * Test to set and get country for the NDC.
     *
     * @return void
     */
    public function testCanSetAndGetCountry()
    {
        $mockCountry = 'United States';

        $this->nationalDataCenter->setCountry($mockCountry);

        $this->assertEquals($mockCountry, $this->nationalDataCenter->getCountry());
    }

    /**
     * Test to set and get email address for the NDC.
     *
     * @return void
     */
    public function testCanSetAndGetEmail()
    {
        $mockEmailAddress = 'help@griidc.org';

        $this->nationalDataCenter->setEmailAddress($mockEmailAddress);

        $this->assertEquals($mockEmailAddress, $this->nationalDataCenter->getEmailAddress());
    }
}
