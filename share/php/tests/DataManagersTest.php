<?php

namespace Pelagos\DataManagers;

/**
 * @runTestsInSeparateProcesses
 */

class DataManagersTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        # add parent directory to include path so tests can be run from anywhere
        set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)));
        require_once 'DataManagers.php';
        require_once 'stubs/ResearchConsortiaStub.php';
        require_once 'stubs/RISStub.php';
        require_once 'stubs/DBUtilsStub.php';
    }

    private function getDataManagerID($dataManager)
    {
        return $dataManager['id'];
    }

    public function testGetDMsFromUserNull()
    {
        $this->assertEquals(array(), getDMsFromUser(null));
    }

    public function testGetDMsFromUserEmptyString()
    {
        $this->assertEquals(array(), getDMsFromUser(''));
    }

    public function testGetDMsFromUserUnknown()
    {
        $this->assertEquals(array(), getDMsFromUser('foobar'));
    }

    public function testGetDMsFromUserNonRIS()
    {
        $this->assertEquals(array(), getDMsFromUser('jdavis'));
    }

    public function testGetDMsFromUserNonRC()
    {
        $this->assertEquals(array(), getDMsFromUser('jbaatz'));
    }

    public function testGetDMsFromUserSingleRC()
    {
        $this->assertEquals(array(778), array_map(array($this,'getDataManagerID'), getDMsFromUser('schen')));
    }

    public function testGetDMsFromUserMultipleRCs()
    {
        $dataManagerIDs = array_map(array($this,'getDataManagerID'), getDMsFromUser('dhastings'));
        sort($dataManagerIDs);
        $this->assertEquals(array(420,943), $dataManagerIDs);
    }

    public function testGetDMsFromUDINull()
    {
        $this->assertEquals(array(), getDMsFromUDI(null));
    }

    public function testGetDMsFromUDIEmptyString()
    {
        $this->assertEquals(array(), getDMsFromUDI(''));
    }

    public function testGetDMsFromUDIInvalidFormat()
    {
        $this->assertEquals(array(), getDMsFromUDI('0123456789012345'));
    }

    public function testGetDMsFromUDIUnknown()
    {
        $this->assertEquals(array(), array_map(array($this,'getDataManagerID'), getDMsFromUDI('R1.x555.115:0002')));
    }

    public function testGetDMsFromUDIValid()
    {
        $this->assertEquals(array(778), array_map(array($this,'getDataManagerID'), getDMsFromUDI('R1.x134.115:0002')));
        $this->assertEquals(array(420), array_map(array($this,'getDataManagerID'), getDMsFromUDI('R1.x135.120:0002')));
    }
}
