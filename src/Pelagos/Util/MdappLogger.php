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
        $dstamp = date('r');
        file_put_contents($this->logfile, "$dstamp: $message\n", FILE_APPEND);
    }
}
