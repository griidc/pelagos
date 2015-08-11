<?php

namespace Pelagos\Entity;

use Symfony\Component\Validator\Validation;
include __dir__."/EntityTest.php";

/**
 * Unit tests for Pelagos\Entity\FundingCycle.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\FundingCycleTest
 */
class FundingCycleTest extends EntityTest
{
    /**
     * Property to hold an instance of FundingCycle for testing.
     *
     * @var FundingCycle $fundingcycle
     */
    protected $fundingcycle;

    /**
     * Property to hold an instance of the Symfony Validator.
     *
     * @var \Symfony\Component\Validator\Validator $validator
     */
    protected $validator;

    /**
     * Static class variable containing a funding cycle name to use for testing.
     *
     * @var string $testFundingCycleName
     */
    protected static $testFundingCycleName = 'TestFundingCycle';

    /**
     * Static class variable containing a funding cycle's description for testing.
     *
     * @var string $testFundingCycleDescription
     */
    protected static $testFundingCycleDescription = 'GRIIDC Test funding cycle FY';

    /**
     * Static class variable containing a funding cycle's URL for testing.
     *
     * @var string $testFundingCycleUrl
     */
    protected static $testFundingCycleUrl = 'http://griidc.org';

    /**
     * Static class variable containing a funding cycle start date to use for testing.
     *
     * @var \Datetime $testFundingCycleStart
     */
    protected static $testFundingCycleStart = '2015-01-01 00:00:00';

    /**
     * Static class variable containing a funding cycle end date to use for testing.
     *
     * @var \Datetime $testFundingCycleEnd
     */
    protected static $testFundingCycleEnd = '2015-12-31 23:59:59';

    /**
     * Static class variable containing a funding cycle's parent organization for testing.
     *
     * @var FundingOrganization $testFundingCycleOrganization
     */
    protected static $testFundingCycleOrganization = null;

    /**
     * Setup for PHPUnit tests.
     *
     * This includes the autoloader and instantiates an instance of FundingCycle.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
        $this->fundingcycle = new FundingCycle;
        $this->fundingcycle->setName(self::$testFundingCycleName);
    }

    /**
     * Test the getId method.
     *
     * This method should always return null because it can not be set (even by the constructor).
     * The id property can only be set when a FundingCycle is instantiated from persistence by Doctrine.
     *
     * @return void
     */
    public function testGetID()
    {
        $this->assertEquals(
            $this->fundingcycle->getId(),
            null
        );
    }

    /**
     * Test the getName method.
     *
     * This method should return the first name that was set in setUp.
     *
     * @return void
     */
    public function testGetName()
    {
        $this->assertEquals(
            $this->fundingcycle->getName(),
            self::$testFundingCycleName
        );
    }

#    /**
#     * Test that FundingCycle is JsonSerializable and serializes to the expected JSON.
#     *
#     * @return void
#     */
#    public function testJsonSerialize()
#    {
#        $timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
#        $timeStampISO = $timeStamp->format(\DateTime::ISO8601);
#        $fundingCycleData = array(
#            'id' => null,
#            'Name' => self::$testFirstName,
#            'lastName' => self::$testLastName,
#            'emailAddress' => self::$testEmailAddress,
#            'creationTimeStamp' => $timeStampISO,
#            'creator' => self::$testCreator,
#            'modificationTimeStamp' => $timeStampISO,
#            'modifier' => self::$testCreator,
#        );
#        $this->person->setCreationTimeStamp($timeStamp);
#        $this->assertEquals(json_encode($personData), json_encode($this->person));
#    }
#
#    /**
#     * Test that we can update single values in FundingCycle with update().
#     *
#     * @return void
#     */
#    public function testUpdateSingleValue()
#    {
#        $this->person->update(array('firstName' => 'newFirstName'));
#        $this->assertEquals('newFirstName', $this->person->getFirstName());
#        $this->person->update(array('lastName' => 'newLastName'));
#        $this->assertEquals('newLastName', $this->person->getLastName());
#        $this->person->update(array('emailAddress' => 'newEmailAddress'));
#        $this->assertEquals('newEmailAddress', $this->person->getEmailAddress());
#        $this->person->update(array('creator' => 'newCreator'));
#        $this->assertEquals('newCreator', $this->person->getCreator());
#    }
#
#    /**
#     * Test that we can update multiple values at once in FundingCycle with update().
#     *
#     * @return void
#     */
#    public function testUpdateMultipleValues()
#    {
#        $this->person->update(
#            array(
#                'firstName' => 'newFirstName2',
#                'lastName' => 'newLastName2',
#                'emailAddress' => 'newEmailAddress2',
#                'creator' => 'newCreator2',
#            )
#        );
#        $this->assertEquals('newFirstName2', $this->person->getFirstName());
#        $this->assertEquals('newLastName2', $this->person->getLastName());
#        $this->assertEquals('newEmailAddress2', $this->person->getEmailAddress());
#        $this->assertEquals('newCreator2', $this->person->getCreator());
#    }
#
#    /**
#     * Test that we can get a FundingCycle's properties as an array.
#     *
#     * @return void
#     */
#    public function testAsArray()
#    {
#        // Get all properties.
#        $this->assertEquals(
#            array(
#                null,
#                self::$testFirstName,
#                self::$testLastName,
#                self::$testEmailAddress,
#                null,
#                self::$testCreator,
#                null,
#                self::$testCreator,
#            ),
#            $this->person->asArray(
#                array(
#                    'id',
#                    'firstName',
#                    'lastName',
#                    'emailAddress',
#                    'creationTimeStamp',
#                    'creator',
#                    'modificationTimeStamp',
#                    'modifier',
#                )
#            )
#        );
#        // Get a subset of the properties.
#        $this->assertEquals(
#            array(
#                self::$testFirstName,
#                self::$testLastName,
#                self::$testEmailAddress,
#            ),
#            $this->person->asArray(
#                array(
#                    'firstName',
#                    'lastName',
#                    'emailAddress',
#                )
#            )
#        );
#        // Should always come back in the order specified in the properties array.
#        $this->assertEquals(
#            array(
#                self::$testEmailAddress,
#                self::$testLastName,
#                self::$testFirstName,
#            ),
#            $this->person->asArray(
#                array(
#                    'emailAddress',
#                    'lastName',
#                    'firstName',
#                )
#            )
#        );
#    }
}
