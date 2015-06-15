<?php

/**
 * A class to get XML metadata files from a file.
 * The file location is stored in the database.
 */
namespace Pelagos\Component\MetadataGenerator;

use \Pelagos\Exception\NotFoundException;
use \Pelagos\Exception\PersistenceException;

class XMLDataFile
{
    private $dbcon = null;

    private $logger = null;
    private static $instance = null;

    /**
     * singleton implementation
     * only one instance of this class allowed
     * per executable unit
     */
    private function __construct()
    {
        require_once 'DBUtils.php';
        $this->dbcon = openDB("GOMRI_RW");
        $this->dbcon->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * singleton implementation
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new XMLDataFile();
        }
        return self::$instance;
    }

    /**
     * Unused - as far as I know
     * @@param string $udi
     * @return string
     */
    private function getFileLocation($udi)
    {
        # load global pelagos config
        $GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

        # load the Common library
        require_once 'Common.php';

        # check for local config file
        if (file_exists(__DIR__ . '/../config.ini')) {
            # merge local config with global config
            $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file(__DIR__ . '/../config.ini', true));
        }

        $metadataPath = $GLOBALS['config']['paths']['data_download'];
        $filePath = $metadataPath . '/' . $udi . '/' . $udi . '.met';

        return $filePath;
    }

    /**
     * Get the location of the xml file from the database.
     * Read it and return it. Throw PersistenceException
     * if there is a database problem.
     * Throw NotFoundException if the database finds a path
     * but the path is not readable.
     * @param string $udi
     * @return bool|string
     * @throws NotFoundException
     * @throws PersistenceException
     * @throws InvalidXmlException
     */
    public function getXML($udi)
    {
        $targetUdi = trim($udi);
        $this->logger = new MetadataLogger("XMLDataFile", $targetUdi);
        $this->logger->setOff();
        $xmlText = false;
        $this->logger->write("XMLDataFile.getXML(" . $targetUdi . ") calling getFileLocation()");
        $path = $this->getFileLocation($targetUdi);
        if ($path == false) {
            throw new NotFoundException("C-2 XMLDataFile No XML found in path: " . $path);
        } elseif (is_readable($path)) {
            $this->logger->write("XMLDataFile.getXML(" . $targetUdi . ") reading file: " . $path);
            $xmlText = file_get_contents($path);
            if ($xmlText === false) {
                throw new NotFoundException("C-2 XMLDataFile file_get_contents is FALSE for path: " . $path);
            }
            $this->logger->write("XMLDataFile.getXML(" . $targetUdi . ") create validator");
            $validator = new XMLValidator();
            $this->logger->write("XMLDataFile.getXML(" . $targetUdi . ") call validator");
            $validator->validate($xmlText);  // throws InvalidXmlException
            $this->logger->write("XMLDataFile.getXML(" . $targetUdi . ") returning xml text");
            return $xmlText;
        }
        throw new NotFoundException("C-2 XMLDataFile No XML found in path: " . $path);
    }
}
