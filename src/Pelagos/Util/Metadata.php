<?php
namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;

/**
 * This is a Metadata utility class.
 */
class Metadata
{
    /**
     * Writes MDAPP-related events to logfile.
     *
     * @param string $message The text to log to file.
     *
     * @return void
     */
    public function writeLog($message)
    {
        $logfileLocation = file($this->getParameter('mdapp_logfile'));
        $dstamp = date('r');
        file_put_contents($logfileLocation, "$dstamp:$message\n", FILE_APPEND);
    }
}
