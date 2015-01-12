<?php
/**
   Harte Research Institute (HRI)
   Gomri Reseach Initiative Information and Data Cooperative (GRIIDC)
   at Texas A&M Corpus Christi (TAMUCC)
   Joe V. Holland

Project: pelagos
datasetsTest.php
User: jvh
1/6/15

**/

namespace Pelagos\datasets;
/**
 * @runTestsInSeparateProcesses
 */

class datasetsTest extends \PHPUnit_Framework_TestCase
{
    private $DBH;

    protected function setUp()
    {
        # add parent directory to include path so tests can be run from anywhere
        set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)));
        require_once 'RIS.php';
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
   //  must be strings since we are parsing for projectId via substring
    private static $pids  = array('029','139','140','141');
    //  expected values for the three categories by the p-ids above
    private static $identified = array(7,83,27,116);
    private static $registered = array(7,53,10,79);
    private static $available = array(1,22,6,42);

    public function  testGetIdentifiedDatasetsByProjectId() {
        $results = array();
        foreach (self::$pids as $pid) {
            $results[] = getIdentifiedDatasetsByProjectId($this->DBH, $pid);
        }
        $this->assertEquals(self::$identified, $results);
    }
    public function  testGetRegisteredDatasetsByProjectId() {

        $results = array();
        foreach (self::$pids as $pid) {
            $results[] = getRegisteredDatasetsByProjectId($this->DBH, $pid);
        }
        $this->assertEquals(self::$registered, $results);
    }
    public function  testGetAvailableDatasetsByProjectId() {
        $results = array();
        foreach (self::$pids as $pid) {
            $results[] = getAvailableDatasetsByProjectId($this->DBH, $pid);
        }
        $this->assertEquals(self::$available, $results);
    }
}
