<?php
/**
 * MetadataXmlFromDB.
 *
 * Generates metadata
 *
 * Texas A&M Corpus Christi
 * Harte Research Institute
 * Gulf (of Mexico) Research Initiative Information & Data Cooperative
 * GRIIDC.
 */

namespace Pelagos\Component\MetadataGenerator;

use \Pelagos\Exception\NotFoundException;
use \Pelagos\Exception\PersistenceException;

/**
 * This class pulls metadata from the persistence layer.
 *
 * This class uses PDO to fetch XML metadata from the database
 * and returns it as XML text.
 */
class MetadataXmlFromDB
{
    /**
     * Database connection variable.
     *
     * @var PDO $dbcon This variable holds the database handle.
     */
    private $dbcon = null;

    const REGISTRY_TABLE_NAME = "public.registry_view";
    //  column names for gomri PGSQL registry table
    const REGISTRY_ID_COL = "registry_id";
    const DATASET_UDI_COL = "dataset_udi";

    // column names for gomri PGSQL metadata table
    // registry id col is named the same in both tables
    const METADATA_XML_COL = "metadata_xml";
    const GEOMETRY_COL = "geom";
    const GEOMETRY_COL_ST_ASTEXT = "st_astext"; // this for converting geometry to readable form
    const EXTENT_DESCRIPTION_COL = "extent_description";

    const METADATA_TABLE_NAME = "public.metadata";

    /**
     * Var to refence self class.
     *
     * @var MetadataXmlFromDB $instance
     */
    private static $instance = null;

    /**
     * Private constructor.
     *
     * Singleton implementation
     * only one instance of this class allowed
     * per executable unit.
     */
    private function __construct()
    {
        require_once 'DBUtils.php';
        $this->dbcon = openDB("GOMRI_RW");
        $this->dbcon->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Singleton implementation.
     *
     * Use this method instead of new MetadataXmlFromDB()
     * This function will return the one instance of this class.
     * If not yet instantiated will create an instance with the
     * private constructor.
     *
     * @return MetadataXmlFromDB self::$instance Itself.
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new MetadataXmlFromDB();
        }
        return self::$instance;
    }

    /**
     * Reads the database metadata table and returns the xml data.
     *
     * @param string $datasetUdi Identifier.
     *
     * @return string - the xml data as a string
     */
    public function getMetadataXmlForDatasetUdi($datasetUdi)
    {
        $targetUdi = trim($datasetUdi);
        $registryId = $this->getRegistryIdForDatasetUdi($targetUdi);
        $xmlData = $this->getMetadataXml($registryId);
        return $xmlData;
    }

    /**
     * Grabs XML from DB.
     *
     * Fetch the the metadata xml from the Metadata table.
     * The key is registry id.
     *
     * @param string $registryId Registry ID.
     *
     * @return string XML text from the metadata table.
     *
     * @throws NotFoundException If the registry row is not found.
     * @throws PersistenceException If persistance layer fails.
     */
    private function getMetadataXml($registryId)
    {
        $query = $this->getMetadataSelectQueryString() . " WHERE " . self::REGISTRY_ID_COL . " = " .
                           $this->wrapInSingleQuotes($registryId) . " LIMIT 1";
        $statement = $this->dbcon->prepare($query);
        $metadataXml = null;
        try {
            if ($statement->execute()) {
                if ($row = $statement->fetch(\PDO::FETCH_ASSOC)) { // if true
                    $metadataXml = $row[self::METADATA_XML_COL];
                    return $metadataXml;
                } // else it is false - not found
                throw new NotFoundException(
                    "No " . self::METADATA_TABLE_NAME . "  found with registry ID " .
                    $registryId
                );
            }
        } catch (\PDOException $pdoEx) {
            throw new PersistenceException( $pdoEx->getMessage());
        }
    }

    /**
     * SQL generating function.
     *
     * This function returns the SELECT statement for all columns
     * in the metatdata table. It is implemented in this way
     * so that the code will reside in only one place to be shared
     * by two or more functions using the product.
     *
     * @return string SQL string.
     *
     * @see    makeSelectQuery(I_Persistable $target)
     * @see    getAll($targetClassName)
     * @see    get(I_Persistable $obj)
     */
    private function getMetadataSelectQueryString()
    {
        return "SELECT " . self::REGISTRY_ID_COL . ", " .
        self::METADATA_XML_COL . " FROM " . self::METADATA_TABLE_NAME . " ";
    }


    /**
     * Fetch the object based on it's unique id.
     *
     * @param string $datasetUdi Input.
     *
     * @return the Registry instance if it exists in the store.
     *
     * @throws NotFoundException If the object is not found.
     * @throws PersistenceException Upon DB failure.
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
                if ($row = $statement->fetch(\PDO::FETCH_ASSOC)) { // if true
                    $registryId = $row[self::REGISTRY_ID_COL];
                    return $registryId;
                } // else it is false - not found
                throw new NotFoundException(
                    "No " . self::REGISTRY_TABLE_NAME .
                    " record found for dataset UDI: " . $datasetUdi
                );
            }
        } catch (\PDOException $pdoEx) {
            throw new PersistenceException(  $pdoEx->getMessage());
        }
    }

    /**
     * SQL generating function.
     *
     * This function returns the SELECT statement for all columns
     * in the metatdata table. It is implemented in this way
     * so that the code will reside in only one place to be shared
     * by two or more functions using the product.
     *
     * @return string Of SQL.
     *
     * @see    makeSelectQuery(I_Persistable $target)
     * @see    getAll($targetClassName)
     * @see    get(I_Persistable $obj)
     */
    private function getRegistryAndUdiSelectQueryString()
    {
        return "SELECT " . self::REGISTRY_ID_COL . ", " .
        self::DATASET_UDI_COL .
        " FROM " . self::REGISTRY_TABLE_NAME . " ";
    }

    /**
     * Compress XML function.
     *
     * @param string $string Input string of XML text.
     *
     * @return string $compressedXml XML in 'compressed' form.
     */
    private function compressXml($string)
    {
        $NA = "N/A";
        $compressedXml = $NA;
        if ($string != null) {
            $compressedXml = $ro = preg_replace('/\s+/', ' ', $string);
        }
        return $compressedXml;
    }

    /**
     * Adds single quotes around a string after trimming whitespace from around the supplied string.
     *
     * @param string $s Input string of arbitrary text.
     *
     * @return string Of original string, trimmed and quoted in single quotes.
     */
    private function wrapInSingleQuotes($s)
    {
        return "'" . trim($s) . "'";
    }
}
