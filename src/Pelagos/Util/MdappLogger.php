<?php
namespace Pelagos\Util;

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
    public function __construct($logfile)
    {
        $this->logfile = $logfile;
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
            FILE_APPEND or LOCK_EX
        );
    }
}
