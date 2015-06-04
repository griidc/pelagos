<?php
/**
 * MetadataXmlFromDB
 * Texas A&M Corpus Christi
 * Harte Research Institute
 * Gulf (of Mexico) Research Initiative Information Data Cooperative
 * GRIIDC
 */



namespace MetadataGenerator;


require_once '../../../share/php/db-utils.lib.php';
require_once "exceptions/DuplicateException.php";
require_once "exceptions/NotFoundException.php";
require_once "exceptions/PersistenceEngineException.php";
require_once "lib/MetadataLogger.php";
use \Exception\NotFoundException as NotFoundException;
use \Exception\PersistenceEngineException as PersistenceEngineException;
use \MetadataGenerator\MetadataLogger as MetadataLogger;
use \PDO as PDO;

class MetadataXmlFromDB
{


    private $dbcon = null;

    const REGISTRY_TABLE_NAME = "public.registry_view";
    //  column names for gomri PGSQL registry table
    const REGISTRY_ID_COL = "registry_id";
    const DATASET_UDI_COL = "dataset_udi";

    //  column names for gomri PGSQL metadata table
    // registry id col is named the same in both tables
    const METADATA_XML_COL = "metadata_xml";
    const GEOMETRY_COL = "geom";
    const GEOMETRY_COL_ST_ASTEXT = "st_astext"; // this for converting geometry to readable form
    const EXTENT_DESCRIPTION_COL = "extent_description";

    const METADATA_TABLE_NAME = "public.metadata";

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
            self::$instance = new MetadataXmlFromDB();
        }
        return self::$instance;
    }

    /**
     * Read the database metadata table and return the
     * xml data
     * @param $datasetUdi
     * @return string - the xml data as a string
     */
    public function getMetadataXmlForDatasetUdi($datasetUdi)
    {

        $targetUdi = trim($datasetUdi);
        $this->logger = new MetadataLogger("metadataXmlFromDB", $targetUdi);
        $this->logger->turnOff();
        $this->logger->log("inside getMetadataXmlForDatasetUdi");
        $registryId = $this->getRegistryIdForDatasetUdi($targetUdi);
        $this->logger->log("  got back " . $registryId);
        $this->logger->log("inside getMetadataXmlForDatasetUdi calling getMetadataXml ");
        $xmlData = $this->getMetadataXml($registryId);
        $this->logger->log("inside getMetadataXmlForDatasetUdi returning  getMetadataXml ");
        return $xmlData;
    }

    /**
     * Fetch the the metadata xml from the Metadata table
     * The key is registry id
     * @param $registryId
     * @return the xml from the meatadata t
     * @throws NotFoundException of the registry row is not found
     * @throws PersistenceEngineException
     */
    private function getMetadataXml($registryId)
    {

        $query = $this->getMetadataSelectQueryString() . " WHERE " . self::REGISTRY_ID_COL . " = " .
                           $this->wrapInSingleQuotes($registryId) . " LIMIT 1";
        $statement = $this->dbcon->prepare($query);
        $metadataXml = null;
        try {
            if ($statement->execute()) {
                if ($row = $statement->fetch(PDO::FETCH_ASSOC)) { // if true
                    $metadataXml = $row[self::METADATA_XML_COL];
                    return $this->compressXml($metadataXml);
                } // else it is false - not found
                throw new NotFoundException(
                    "C-1: " . "No " . self::METADATA_TABLE_NAME . "  found with registry ID " .
                    $registryId
                );
            }
        } catch (PDOException $pdoEx) {
            throw new PersistenceEngineException("C-1: " . $pdoEx->getMessage());
        }
    }

    /**
     * This function returns the SELECT statement for all columns
     * in the metatdata table. It is implemented in this way
     * so that the code will reside in only one place to be shared
     * by two or more functions using the product.
     * @return string
     * @see makeSelectQuery(I_Persistable $target)
     * @see getAll($targetClassName)
     * @see get(I_Persistable $obj)
     */
    private function getMetadataSelectQueryString()
    {
        return "SELECT " . self::REGISTRY_ID_COL . ", " .
        self::METADATA_XML_COL . " FROM " . self::METADATA_TABLE_NAME . " ";
    }


    /**
     * Fetch the object based on it's unique id.
     * @param I_Persistable $obj
     * @return the Registry instance if it exists in the store
     * @throws NotFoundException of the object is not found
     * @throws PersistenceEngineException
     */
    private function getRegistryIdForDatasetUdi($datasetUdi)
    {

        $query = $this->getRegistryAndUdiSelectQueryString() . " WHERE " . self::DATASET_UDI_COL . " = " .
                $this->wrapInSingleQuotes($datasetUdi) . " LIMIT 1";

        $this->logger->log("getRegistryIdForDatasetUdi() query: " . $query);
        $statement = $this->dbcon->prepare($query);
        $registryId = null;
        try {
            if ($statement->execute()) {
                if ($row = $statement->fetch(PDO::FETCH_ASSOC)) { // if true
                    $registryId = $row[self::REGISTRY_ID_COL];
                    $this->logger->log("getRegistryIdForDatasetUdi() returning registry " . $registryId);
                    return $registryId;
                } // else it is false - not found
                $this->logger->log("getRegistryIdForDatasetUdi() throwing NotFoundException");
                throw new NotFoundException(
                    "C-1: " . "No " . self::REGISTRY_TABLE_NAME .
                    " record found for dataset UDI: " . $datasetUdi
                );
            }
        } catch (PDOException $pdoEx) {
            $this->logger->log("getRegistryIdForDatasetUdi() PDOException: " . $pdoEx->getMessage());
            throw new PersistenceEngineException("C-1: " . $pdoEx->getMessage());
        }
    }

    /**
     * This function returns the SELECT statement for all columns
     * in the metatdata table. It is implemented in this way
     * so that the code will reside in only one place to be shared
     * by two or more functions using the product.
     * @return string
     * @see makeSelectQuery(I_Persistable $target)
     * @see getAll($targetClassName)
     * @see get(I_Persistable $obj)
     */
    private function getRegistryAndUdiSelectQueryString()
    {
        return "SELECT " . self::REGISTRY_ID_COL . ", " .
        self::DATASET_UDI_COL .
        " FROM " . self::REGISTRY_TABLE_NAME . " ";
    }

    private function wrapInSingleQuotes($s)
    {
        return "'" . trim($s) . "'";
    }

    private function compressXml($string)
    {
        $NA = "N/A";
        $compressedXml = $NA;
        if ($string != null) {
            $compressedXml = $ro = preg_replace('/\s+/', ' ', $string);
        }
        return $compressedXml;
    }
}
