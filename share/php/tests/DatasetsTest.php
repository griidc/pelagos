<?php
/**
   Harte Research Institute (HRI)
   Gomri Reseach Initiative Information and Data Cooperative (GRIIDC)
   at Texas A&M Corpus Christi (TAMUCC)
   Joe V. Holland

Project: pelagos
DatasetsTest.php
User: jvh
1/6/15

**/

namespace Pelagos;

/**
 * @runTestsInSeparateProcesses
 */

class DatasetsTest extends \PHPUnit_Framework_TestCase
{
    private $DBH;

    protected function setUp()
    {
        # add parent directory to include path so tests can be run from anywhere
        set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)));
        require_once 'DBUtils.php';
        require_once "datasets.php";
        # open a database connetion to RIS
        $this->DBH = OpenDB('GOMRI_RW');
    }

    protected function tearDown()
    {
        # close database connection
        $this->DBH = null;
    }

   //  constants project ids for testing
    private static $pids  = array(29,139,140,141);
    //  expected values for the three categories by the p-ids above
    private static $identified = array(7,61,10,82);
    private static $registered = array(5,30,6,62);
    private static $available = array(1,22,6,43);

    public function testGetIdentifiedDatasetsByProjectId()
    {
        $results = array();
        foreach (self::$pids as $pid) {
            $results[] = getIdentifiedDatasetsByProjectId($this->DBH, $pid);
        }
        $this->assertEquals(self::$identified, $results);
    }
    public function testGetRegisteredDatasetsByProjectId()
    {

        $results = array();
        foreach (self::$pids as $pid) {
            $results[] = getRegisteredDatasetsByProjectId($this->DBH, $pid);
        }
        $this->assertEquals(self::$registered, $results);
    }
    public function testGetAvailableDatasetsByProjectId()
    {
        $results = array();
        foreach (self::$pids as $pid) {
            $results[] = getAvailableDatasetsByProjectId($this->DBH, $pid);
        }
        $this->assertEquals(self::$available, $results);
    }
}
