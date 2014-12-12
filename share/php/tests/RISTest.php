<?php

namespace Pelagos\RIS;

/**
 * @runTestsInSeparateProcesses
 */

class RISTest extends \PHPUnit_Framework_TestCase
{
    private $DBH;

    protected function setUp()
    {
        # add parent directory to include path so tests can be run from anywhere
        set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)));
        require_once 'RIS.php';
        require_once 'DBUtils.php';
        # open a database connetion to RIS
        $this->DBH = OpenDB('RIS_RO');
    }

    protected function tearDown()
    {
        # close database connection
        $this->DBH = null;
    }

    private function getDataManagerID($dataManager)
    {
        return $dataManager['id'];
    }

    public function testGetProjectDetailsProjectId()
    {
        $project = array(
            'ID' => '132',
            'Abbr' => 'ECOGIG',
            'Title' => 'Ecosystem Impacts of Oil and Gas Inputs to the Gulf (ECOGIG)',
            'Abstract' => 'The ECOGIG consortium brings together physical oce',
            'StartDate' => '2011-09-01',
            'EndDate' => '2015-04-04',
            'Location' => '',
            'Fund_Src' => '7',
            'Fund_Abbr' => 'RFP-I',
            'Fund_Name' => 'Year 2-4 Consortia Grants (RFP-I)',
            'SubTasks' => '8'
        );
        $details = getProjectDetails($this->DBH, array('projectid=132'));
        $projectDetails = $details[0];
        if (array_key_exists('Abstract', $projectDetails)) {
            $projectDetails['Abstract'] = substr($projectDetails['Abstract'], 0, 50);
        }
        $this->assertEquals($project, $projectDetails);
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

    public function testGetDMsFromRISUser()
    {
        $DMs = getDMsFromRISUser($this->DBH, 400);
        $this->assertEquals(array(778), array_map(array($this,'getDataManagerID'), $DMs));
    }

    public function testGetDMsFromRISUserMultiple()
    {
        $dataManagerIDs = array_map(array($this,'getDataManagerID'), getDMsFromRISUser($this->DBH, 87));
        sort($dataManagerIDs);
        $this->assertEquals(array(420,943), $dataManagerIDs);
    }
}
