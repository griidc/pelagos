<?php
namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;
use Pelagos\Entity\Person;

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
        $dateTimeFormat = 'F j, Y, g:i:s a';
        $timestamp = new \DateTime('now', new \DateTimeZone($tz));
        $successfulWrite = file_put_contents(
            $this->logfile,
            $timestamp->format($dateTimeFormat) . ": $message\n",
            (FILE_APPEND | LOCK_EX)
        );
    }

    /**
     * This function standardizes the messaging from review actions as written to the mdapp log.
     *
     * @param string $userid       The operator (DRPM) making the change.
     * @param string $beforeStatus The review status before the change.
     * @param string $afterStatus  The review status after the change.
     * @param string $udi          The dataset being reviewed.
     *
     * @return string        The formated message to be logged.
     */
    public function createReviewChangeMessage($userid, $beforeStatus, $afterStatus, $udi)
    {
        return
            $userid . ' changed review status from ' .
             $beforeStatus . ' to ' . $afterStatus . ' UDI: ' . $udi . ' ';
    }
}
