<?php

namespace Pelagos\DataManagers;

class DataManagersTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        require 'DataManagers.php';
        require 'stubs/ResearchConsortiaStub.php';
        require 'stubs/RISStub.php';
    }

    protected function tearDown()
    {
        $funcs = array(
            # functions declared in stubs/RISStub.php
            'getProjectDetails',
            'getTaskDetails',
            'getPeopleDetails',
            'getPeopleList',
            'getPeopleLI',
            'getInstitutionDetails',
            'getFundingSources',
            'getDMsFromRC',
            'getRCsFromRISUser',
            # functions declared in stubs/ResearchConsortia.php
            'getRCFromUDI',
            'getRCsFromUser',
            # functions declared in DataManagers.php
            'getDMsFromUser',
            'getDMsFromUDI'
        );
        # remove all functions declared during setUp()
        foreach ($funcs as $func) {
            if (function_exists($func)) {
                runkit_function_remove($func);
            }
        }
    }

    private function getDataManagerID($dataManager)
    {
        return $dataManager['ID'];
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
