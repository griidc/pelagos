<?php
namespace Tests\unit\Pelagos\Util;

use PHPUnit\Framework\TestCase;

use Pelagos\Util\PelagosUtilities;

/**
 * Unit test(s) for Pelagos\Util\PelagosUtilitiesTest.
 *
 * @group Pelagos
 * @group Pelagos\Util
 */
class PelagosUtilitiesTest extends TestCase
{
    /**
     * Holds a PelagosUtilties class under test.
     *
     * @var Pelagos\Util\PelagosUtilities
     */
    protected $util;

    /**
     * Unit test setup.
     *
     * @return void
     */
    public function setUp()
    {
        //$this->util = new PelagosUtilities;
    }

    /**
     * Tests the static nullOrNonemethod.
     *
     * @return void
     */
    public function testPopulateDatasetSubmissionWithXMLValues()
    {
        $this->assertTrue(PelagosUtilities::nullOrNone(array(1, 1, 1)));
        $this->assertTrue(PelagosUtilities::nullOrNone(array(null, null, null)));
        $this->assertFalse(PelagosUtilities::nullOrNone(array(null, 1, 1)));
        $this->assertFalse(PelagosUtilities::nullOrNone(array(1, null ,1)));
        $this->assertFalse(PelagosUtilities::nullOrNone(array(1, 1, null)));
    }
}
