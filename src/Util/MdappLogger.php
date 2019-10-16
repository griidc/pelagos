<?php
namespace App\Util;

use Doctrine\ORM\EntityManager;

/**
 * This is a Metadata utility class.
 */
class MdappLogger
{
    /**
     * String of full filepath to logfile.
     *
     * @var string
     */
    protected $logfile;

    /**
     * Constructor for dependency injection.
     *
     * @param string $logfile String with filepath.
     */
    public function __construct(string $mdappLogfile)
    {
        $this->logfile = $mdappLogfile;
    }

    /**
     * Writes MDAPP-related events to logfile.
     *
     * @param string $message The text to log to file.
     *
     * @return void
     */
    public function writeLog($message)
    {
        $tz = ini_get('date.timezone');
        $timestamp = new \DateTime('now', new \DateTimeZone($tz));
        $successfulWrite = file_put_contents(
            $this->logfile,
            $timestamp->format('r') . ": $message\n",
            (FILE_APPEND | LOCK_EX)
        );
    }

    /**
     * Get logfile entries for particular dataset UDI.
     *
     * @param string $udi The dataset UDI identifier.
     *
     * @return array Containing log entries for that UDI.
     */
    public function getlogEntriesByUdi($udi)
    {

        $rawlog = file($this->logfile);
        $entries = array();
        $entries = array_values(preg_grep("/$udi/i", $rawlog));
        return $entries;
    }
}
