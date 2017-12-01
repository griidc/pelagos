<?php
namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;

/**
 * This is a Metadata utility class.
 */
class MdappLogger
{
    /**
     * Constant of the number of write attempts made to write to log.
     */
    const RETRIES = 5;

    /**
     * Constant of number fo seconds to sleep inbetween log write attempts.
     */
    const SLEEPYTIME = 1;

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
     * @throws \Exception If syslog cannot be written.
     *
     * @return void
     */
    public function writeLog($message)
    {
        $tz = ini_get('date.timezone');
        $attemptCount = 0;
        $timestamp = new \DateTime('now', new \DateTimeZone($tz));
        $successfulWrite = file_put_contents($this->logfile, $timestamp->format('r') . ": $message\n", FILE_APPEND);
        while (false === $successfulWrite) {
            if ($attemptCount < RETRIES) {
                sleep(SLEEPYTIME);
                $successfulWrite = file_put_contents(
                    $this->logfile,
                    $timestamp->format('r') . ": $message\n",
                    FILE_APPEND
                );
                $attemptCount++;
            } else {
                $syslogSuccess = syslog(
                    LOG_ERR,
                    'Failed to write to Mdapp log: ' . $timestamp->format('r') . ": $message\n"
                );
                if (false == $syslogSuccess) {
                    throw new \Exception('Could not write to syslog. Something is seriously wrong.');
                }
            }
        }
    }
}
