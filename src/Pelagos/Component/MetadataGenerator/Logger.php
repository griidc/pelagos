<?php

namespace Pelagos\Component\MetadataGenerator;

/**
 * A class provided as a simple logging mechanism.
 *
 * Construct with new Logger() or new Logger(filename);
 * activate logging with logger->setOn();
 * log messages with logger->write(msg) or logger->log(message);
 */
class Logger
{
    /**
     * Flag whether this is first time this run.
     *
     * @var bool $firstTime
     */
    private $firstTime = true;

    /**
     * The default file name to log to.
     *
     * @var string $defaultFileName
     */
    private static $defaultFileName = 'logger.txt';

    /**
     * The name of the file in which the log is stored.
     *
     * @var string $fileName
     */
    private $fileName = null;

    /**
     * A toggle used to turn logging on and off.
     *
     * @var bool $onOffSwitch
     */
    private $onOffSwitch = false;

    /**
     * A character be append to the end of each logged message.
     *
     * @var string $lineBreakCharacter
     */
    private $lineBreakCharacter = "\n";

    /**
     * Constructor for Logger.
     *
     * @param string $filename The file name to log to.
     */
    public function __construct($filename = null)
    {
        $this->fileName = self::$defaultFileName;
        if ($filename != null) {
            $this->fileName = $filename;
        }
    }

    /**
     * Returns the default file name.
     *
     * @return string
     */
    public static function getDefaultFileName()
    {
        return self::$defaultFileName;
    }

    /**
     * Send the message to the log.
     *
     * If the logger is turned off no message is written.
     *
     * @param string $msg The message to write.
     *
     * @return void
     */
    public function write($msg)
    {
        if (self::isOn()) {
            ob_start();
            var_dump($msg);
            $output = ob_get_clean();

            $outputFile = './' . $this->fileName;

            // If this is first time this run open and truncate.
            if ($this->firstTime) {
                $filehandle = fopen($outputFile, 'w') or die('File creation error.');
                fclose($filehandle);
            }
            $this->firstTime = false;
            $filehandle = fopen($outputFile, 'a') or die('File creation error.');
            fwrite($filehandle, $output . $this->lineBreakCharacter);
            fclose($filehandle);
        }
    }

    /**
     * Send the message to the log.
     *
     * If the logger is turned off no message is written.
     *
     * @param string $msg The message to write.
     *
     * @return void
     */
    public function log($msg)
    {
        self::write($msg);
    }

    /**
     * Check if logging is turned on.
     *
     * Returns true if logging is turned on.
     * Returns false if loggin is turned off
     *
     * @return boolean
     */
    public function isOn()
    {
        return ($this->onOffSwitch == true);
    }

    /**
     * Turn logging on.
     *
     * @return void
     */
    public function setOn()
    {
        $this->onOffSwitch = true;
    }

    /**
     * Turn off logging.
     *
     * @return void
     */
    public function setOff()
    {
        $this->onOffSwitch = false;
    }

    /**
     * Return the name of the file that this logger is writing.
     *
     * @return null|string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Return a descriptive string containing the name of the file that this logger is writing.
     *
     * @return string
     */
    public function __toString()
    {
        return 'logger: filename: ' . $this->getFileName();
    }
}
