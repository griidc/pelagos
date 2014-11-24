<?php

namespace Pelagos\RIS;

class RISTest extends \PHPUnit_Framework_TestCase
{
    private $DBH;

    protected function setUp()
    {
        require 'RIS.php';
        require 'db-utils.lib.php';
        # open a database connetion to RIS
        $this->DBH = OpenDB('RIS_RO');
    }

    protected function tearDown()
    {
        # close database connection
        $this->DBH = null;
        $funcs = array(
            # functions declared in db-utils.lib.php
            'OpenDB',
            # functions declared in RIS.php
            'getProjectDetails',
            'getTaskDetails',
            'getPeopleDetails',
            'getPeopleList',
            'getPeopleLI',
            'getInstitutionDetails',
            'getFundingSources',
            'getDMsFromRC',
            'getRCsFromRISUser'
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

    public function testGetDMsFromRCNull()
    {
        $this->assertEquals(array(), getDMsFromRC($this->DBH, null));
    }

    public function testGetDMsFromRCZero()
    {
        $this->assertEquals(array(), getDMsFromRC($this->DBH, 0));
    }

    public function testGetDMsFromRCNegative()
    {
        $this->assertEquals(array(), getDMsFromRC($this->DBH, -1));
    }

    public function testGetDMsFromRCEmptyString()
    {
        $this->assertEquals(array(), getDMsFromRC($this->DBH, ''));
    }

    public function testGetDMsFromRCNotAnRC()
    {
        $this->assertEquals(array(), getDMsFromRC($this->DBH, 222));
    }

    public function testGetDMsFromRCValid()
    {
        $this->assertEquals(array(2818), array_map(array($this,'getDataManagerID'), getDMsFromRC($this->DBH, 132)));
        $this->assertEquals(array(778), array_map(array($this,'getDataManagerID'), getDMsFromRC($this->DBH, 134)));
        $this->assertEquals(array(420), array_map(array($this,'getDataManagerID'), getDMsFromRC($this->DBH, 135)));
        $this->assertEquals(array(573), array_map(array($this,'getDataManagerID'), getDMsFromRC($this->DBH, 137)));
        $this->assertEquals(array(943), array_map(array($this,'getDataManagerID'), getDMsFromRC($this->DBH, 138)));
        $this->assertEquals(array(1544), array_map(array($this,'getDataManagerID'), getDMsFromRC($this->DBH, 139)));
        $this->assertEquals(array(951), array_map(array($this,'getDataManagerID'), getDMsFromRC($this->DBH, 140)));
        $this->assertEquals(array(467), array_map(array($this,'getDataManagerID'), getDMsFromRC($this->DBH, 141)));
    }

    public function testGetRCsFromRISUserNull()
    {
        $this->assertEquals(array(), getRCsFromRISUser($this->DBH, null));
    }

    public function testGetRCsFromRISUserZero()
    {
        $this->assertEquals(array(), getRCsFromRISUser($this->DBH, 0));
    }

    public function testGetRCsFromRISUserNegative()
    {
        $this->assertEquals(array(), getRCsFromRISUser($this->DBH, -1));
    }

    public function testGetRCsFromRISUserEmptyString()
    {
        $this->assertEquals(array(), getRCsFromRISUser($this->DBH, ''));
    }

    public function testGetRCsFromRISUserNonNumericString()
    {
        $this->assertEquals(array(), getRCsFromRISUser($this->DBH, 'abc'));
    }

    public function testGetRCsFromRISUserUnknown()
    {
        $this->assertEquals(array(), getRCsFromRISUser($this->DBH, 99999));
    }

    public function testGetRCsFromRISUserValid()
    {
        $this->assertEquals(array(134), getRCsFromRISUser($this->DBH, 400));
    }

    public function testGetRCsFromRISUserValidNumericString()
    {
        $this->assertEquals(array(134), getRCsFromRISUser($this->DBH, '400'));
    }

    public function testGetRCsFromRISUserMultipleRCs()
    {
        $RCs = getRCsFromRISUser($this->DBH, 87);
        sort($RCs);
        $this->assertEquals(array(135,138), $RCs);
    }
}
