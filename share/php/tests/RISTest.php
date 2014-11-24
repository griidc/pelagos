<?php

namespace Pelagos\RIS;

class RISTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        require 'RIS.php';
    }

    protected function tearDown()
    {
        $funcs = array(
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
        $this->assertEquals(array(), getDMsFromRC(null));
    }

    public function testGetDMsFromRCZero()
    {
        $this->assertEquals(array(), getDMsFromRC(0));
    }

    public function testGetDMsFromRCNegative()
    {
        $this->assertEquals(array(), getDMsFromRC(-1));
    }

    public function testGetDMsFromRCEmptyString()
    {
        $this->assertEquals(array(), getDMsFromRC(''));
    }

    public function testGetDMsFromRCNotAnRC()
    {
        $this->assertEquals(array(), getDMsFromRC(222));
    }

    public function testGetDMsFromRCValid()
    {
        $this->assertEquals(array(2818), array_map(array($this,'getDataManagerID'), getDMsFromRC(132)));
        $this->assertEquals(array(778), array_map(array($this,'getDataManagerID'), getDMsFromRC(134)));
        $this->assertEquals(array(420), array_map(array($this,'getDataManagerID'), getDMsFromRC(135)));
        $this->assertEquals(array(573), array_map(array($this,'getDataManagerID'), getDMsFromRC(137)));
        $this->assertEquals(array(943), array_map(array($this,'getDataManagerID'), getDMsFromRC(138)));
        $this->assertEquals(array(1544), array_map(array($this,'getDataManagerID'), getDMsFromRC(139)));
        $this->assertEquals(array(951), array_map(array($this,'getDataManagerID'), getDMsFromRC(140)));
        $this->assertEquals(array(467), array_map(array($this,'getDataManagerID'), getDMsFromRC(141)));
    }

    public function testGetRCsFromRISUserNull()
    {
        $this->assertEquals(array(), getRCsFromRISUser(null));
    }

    public function testGetRCsFromRISUserZero()
    {
        $this->assertEquals(array(), getRCsFromRISUser(0));
    }

    public function testGetRCsFromRISUserNegative()
    {
        $this->assertEquals(array(), getRCsFromRISUser(-1));
    }

    public function testGetRCsFromRISUserEmptyString()
    {
        $this->assertEquals(array(), getRCsFromRISUser(''));
    }

    public function testGetRCsFromRISUserNonNumericString()
    {
        $this->assertEquals(array(), getRCsFromRISUser('abc'));
    }

    public function testGetRCsFromRISUserUnknown()
    {
        $this->assertEquals(array(), getRCsFromRISUser(99999));
    }

    public function testGetRCsFromRISUserValid()
    {
        $this->assertEquals(array(134), getRCsFromRISUser(400));
    }

    public function testGetRCsFromRISUserValidNumericString()
    {
        $this->assertEquals(array(134), getRCsFromRISUser('400'));
    }

    public function testGetRCsFromRISUserMultipleRCs()
    {
        $RCs = getRCsFromRISUser(87);
        sort($RCs);
        $this->assertEquals(array(135,138), $RCs);
    }
}
