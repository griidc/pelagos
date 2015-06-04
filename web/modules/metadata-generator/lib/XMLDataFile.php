<?php

namespace MetadataGenerator;

require_once "exceptions/NotFoundException.php";
require_once "exceptions/PersistenceEngineException.php";
require_once "exceptions/InvalidXmlException.php";
require_once '../../../share/php/db-utils.lib.php';
require_once "lib/MetadataLogger.php";
require_once "lib/XMLValidator.php";

use \Exception\NotFoundException as NotFoundException;
use \Exception\PersistenceEngineException as PersistenceEngineException;
use \PDO as PDO;
use \MetadataGenerator\MetadataLogger as MetadataLogger;
use \MetadataGenerator\XMLValidator as XMLValidator;

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
        $this->dbcon = OpenDB("GOMRI_RW");
        $this->dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

    private function getLocationUgly($udi)
    {
        # load global pelagos config
        $GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

        # load Common library from global share
        require_once('/../../../../share/php/Common.php');

        # check for local config file
        if (file_exists('/config.ini')) {
            # merge local config with global config
            $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file('config.ini', true));
        }

        $metadataPath = $GLOBALS['config']['paths']['data_download'];
        $filePath = $metadataPath . '/' . $udi . '/' . $udi . '.met';

        return $filePath;
    }

    private function getLocationFromDB($udi)
    {
        $targetUdi = trim($udi);
        $this->logger->write("XMLDataFile.getLocationFromDB(" . $targetUdi . ")");

        # load global pelagos config
        $env = array();
        $env['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);
        // $GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);
        # load Common libraries

        # check for local config file
        if (file_exists('/config.ini')) {
            # merge local config with global config
            $env['config'] = configMerge($env, parse_ini_file('config.ini', true));
        }

        $this->logger->write("XMLDataFile.getLocationFromDB(" . $targetUdi . ")");

        $metadataPath = $env['config']['paths']['data_download'];

        $sql = "SELECT REGEXP_REPLACE(
                                        REGEXP_REPLACE(
                                                        dataset_metadata,
                                                        '-metadata.xml$', ''),
                                        '-',
                                        ':')
               AS dataset_metadata
               FROM registry_view
               WHERE dataset_udi = :udi";


        $sth = $this->dbcon->prepare($sql);
        $sth->bindparam(":udi", $targetUdi);
        try {
            $sth->execute();
        } catch (\PDOException $e) {
            throw new PersistenceEngineException("C-2 XMLDataFile " . $e->getMessage());
        }

        $data = $sth->fetchAll();

        if (($sth->rowCount() > 0)) {
            $filepath = $metadataPath . '/' . $data[0][0] . '/' . $data[0][0] . '.met';
            $this->logger->write("XMLDataFile.getLocationFromDB(" . $targetUdi . ") returning file path: " . $filepath);

            return $filepath;
        } else {
            $this->logger->write("XMLDataFile.getLocationFromDB(" . $targetUdi . ") returning FALSE: ");
            return false;
        }

    }

    /**
     * Get the location of the xml file from the database.
     * Read it and return it. Throw PersistenceEngineException
     * if there is a database problem.
     * Throw NotFoundException if the database finds a path
     * but the path is not readable.
     * @param $udi
     * @return bool|string
     * @throws NotFoundException
     * @throws PersistenceEngineException
     * @throws InvalidXmlException
     */
    public function getXML($udi)
    {
        $targetUdi = trim($udi);
        $this->logger = new MetadataLogger("XMLDataFile", $targetUdi);
        $this->logger->turnOff();
        $xmlText = false;
        $this->logger->write("XMLDataFile.getXML(" . $targetUdi . ") calling getLocationFromDB()");
        $path = $this->getLocationFromDB($targetUdi);
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
