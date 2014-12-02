<?php

namespace Pelagos\ResearchConsortia;

/**
 * @runTestsInSeparateProcesses
 */

class ResearchConsortiaTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        # add parent directory to include path so tests can be run from anywhere
        set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)));
        require_once 'ResearchConsortia.php';
        require_once 'stubs/RISStub.php';
        require_once 'stubs/DBUtilsStub.php';
        require_once 'stubs/ldapStub.php';
        require_once 'stubs/datasetsStub.php';
    }

    public function testGetRCsFromUserNull()
    {
        $this->assertEquals(array(), getRCsFromUser(null));
    }

    public function testGetRCsFromUserEmptyString()
    {
        $this->assertEquals(array(), getRCsFromUser(''));
    }

    public function testGetRCsFromUserUnknown()
    {
        $this->assertEquals(array(), getRCsFromUser('foobar'));
    }

    public function testGetRCsFromUserNonRIS()
    {
        $this->assertEquals(array(), getRCsFromUser('jdavis'));
    }

    public function testGetRCsFromUserNonRC()
    {
        $this->assertEquals(array(), getRCsFromUser('jbaatz'));
    }

    public function testGetRCsFromUserSingleRC()
    {
        $this->assertEquals(array(100), getRCsFromUser('user1'));
    }

    public function testGetRCsFromUserMultipleRCs()
    {
        $RCs = getRCsFromUser('user2');
        sort($RCs);
        $this->assertEquals(array(200,300), $RCs);
    }
}
