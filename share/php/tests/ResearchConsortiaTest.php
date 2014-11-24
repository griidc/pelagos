<?php

namespace Pelagos\ResearchConsortia;

class ResearchConsortiaTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        require 'ResearchConsortia.php';
        require 'stubs/RISStub.php';
        $GLOBALS['ldap'] = parse_ini_file('tests/ldap.ini', true);
    }

    protected function tearDown()
    {
        # clean out $GLOBALS['ldap']
        unset($GLOBALS['ldap']);
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
            # functions declared in ResearchConsortia.php
            'getRCFromUDI',
            'getRCsFromUser'
        );
        # remove all functions declared during setUp()
        foreach ($funcs as $func) {
            if (function_exists($func)) {
                runkit_function_remove($func);
            }
        }
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
        $this->assertEquals(array(134), getRCsFromUser('schen'));
    }

    public function testGetRCsFromUserMultipleRCs()
    {
        $RCs = getRCsFromUser('dhastings');
        sort($RCs);
        $this->assertEquals(array(135,138), $RCs);
    }

    public function testGetRCFromUDINull()
    {
        $this->assertEquals(null, getRCFromUDI(null));
    }

    public function testGetRCFromUDIEmptyString()
    {
        $this->assertEquals(null, getRCFromUDI(''));
    }

    public function testGetRCFromUDIInvalidFormat()
    {
        $this->assertEquals(null, getRCFromUDI('0123456789012345'));
    }

    public function testGetRCFromUDIUnknown()
    {
        $this->assertEquals(null, getRCFromUDI('R1.x555.115:0002'));
    }

    public function testGetRCFromUDIValid()
    {
        $this->assertEquals(134, getRCFromUDI('R1.x134.115:0002'));
        $this->assertEquals(135, getRCFromUDI('R1.x135.120:0002'));
    }
}
