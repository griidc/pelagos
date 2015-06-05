<?php
/**
 * Logger
 * Texas A&M Corpus Christi
 * Harte Research Institute
 * Gulf (of Mexico) Research Initiative Information Data Cooperative
 * GRIIDC
 */

/**
 * A class provided as a simple logging mechanism.
 * Construct with new Logger() or new Logger(filename);
 * activate logging with logger->setOn();
 * log messages with logger->write(msg) or logger->log(message);
 */
namespace MetadataGenerator;


class Logger
{
    private $firstTime = true;
    private static $defaultFileName = "logger.txt";
    //  the name of the file in which the log is stored
    private $fileName = null;
    //  a toggle used to turn logging on and off
    private $onOffSwitch = false;
    // a character be append to the end of each logged message
    private $lineBreakCharacter = "\n";

    public function __construct($filename = null)
    {
        $this->fileName = self::$defaultFileName;
        if ($filename != null) {
            $this->fileName = $filename;
        }
    }

    /**
     * @return string
     */
    public static function getDefaultFileName()
    {
        return self::$defaultFileName;
    }

    /**
     * Send the message to the log.
     * If the logger is turned off no message is written.
     * @param $msg
     */
    public function write($msg)
    {
        if (self::isOn()) {
            ob_start();
            var_dump($msg);
            $output = ob_get_clean();

            $outputFile = "./" . $this->fileName;

            /**  if this is first time this run open and truncate  */
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
     * If the logger is turned off no message is written.
     * @param $msg
     */
    public function log($msg)
    {
        self::write($msg);
    }

    /**
     * Is loggin turned on?
     * Returns true if logging is turned on.
     * Returns false if loggin is turned off
     * @return boolean
     */
    public function isOn()
    {
        return ($this->onOffSwitch == true);
    }

    /**
     * Turn loggin on
     * @param boolean $onOffSwitch
     */
    public function setOn()
    {
        $this->onOffSwitch = true;
    }

    /**
     * Turn off logging
     * @param boolean $onOffSwitch
     */
    public function setOff()
    {
        $this->onOffSwitch = false;
    }

    /**
     * Return the name of the file that this
     * logger is writing.
     * @return null|string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    public function __toString()
    {
        return "logger: filename: " . $this->getFileName();
    }
}
