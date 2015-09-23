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

    const REGISTRY_TABLE_NAME = 'public.registry_view';
    // Column names for gomri PGSQL registry table.
    const REGISTRY_ID_COL = 'registry_id';
    const DATASET_UDI_COL = 'dataset_udi';

    // Column names for gomri PGSQL metadata table.
    // Registry id col is named the same in both tables.
    const METADATA_XML_COL = 'metadata_xml';
    const GEOMETRY_COL = 'geom';
    // This for converting geometry to readable form.
    const GEOMETRY_COL_ST_ASTEXT = 'st_astext';
    const EXTENT_DESCRIPTION_COL = 'extent_description';

    const METADATA_TABLE_NAME = 'public.metadata';

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
        $this->dbcon = openDB('GOMRI_RW');
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
     * @throws NotFoundException If the registry row is not found.
     * @throws PersistenceException If persistance layer fails.
     *
     * @return string XML text from the metadata table.
     */
    private function getMetadataXml($registryId)
    {
        $query = $this->getMetadataSelectQueryString() . ' WHERE ' . self::REGISTRY_ID_COL . ' = ' .
                           $this->wrapInSingleQuotes($registryId) . ' LIMIT 1';
        $statement = $this->dbcon->prepare($query);
        $metadataXml = null;
        try {
            if ($statement->execute()) {
                $row = $statement->fetch(\PDO::FETCH_ASSOC);
                if ($row !== false) {
                    // If true.
                    $metadataXml = $row[self::METADATA_XML_COL];
                    return $metadataXml;
                }
                // Else it is false - not found.
                throw new NotFoundException(
                    'No ' . self::METADATA_TABLE_NAME . '  found with registry ID ' .
                    $registryId
                );
            }
        } catch (\PDOException $pdoEx) {
            throw new PersistenceException($pdoEx->getMessage());
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
     * @see makeSelectQuery(I_Persistable $target)
     * @see getAll($targetClassName)
     * @see get(I_Persistable $obj)
     *
     * @return string SQL string.
     */
    private function getMetadataSelectQueryString()
    {
        return 'SELECT ' . self::REGISTRY_ID_COL . ', ' .
        self::METADATA_XML_COL . ' FROM ' . self::METADATA_TABLE_NAME . ' ';
    }

    /**
     * Fetch the object based on it's unique id.
     *
     * @param string $datasetUdi Input.
     *
     * @throws NotFoundException If the object is not found.
     * @throws PersistenceException Upon DB failure.
     *
     * @return the Registry instance if it exists in the store.
     */
    private function getRegistryIdForDatasetUdi($datasetUdi)
    {
        $query = $this->getRegistryAndUdiSelectQueryString() . ' WHERE ' . self::DATASET_UDI_COL . ' = ' .
                $this->wrapInSingleQuotes($datasetUdi) . ' LIMIT 1';

        $statement = $this->dbcon->prepare($query);
        $registryId = null;
        try {
            if ($statement->execute()) {
                $row = $statement->fetch(\PDO::FETCH_ASSOC);
                if ($row !== false) {
                    // If true.
                    $registryId = $row[self::REGISTRY_ID_COL];
                    return $registryId;
                }
                // Else it is false - not found.
                throw new NotFoundException(
                    'No ' . self::REGISTRY_TABLE_NAME .
                    ' record found for dataset UDI: ' . $datasetUdi
                );
            }
        } catch (\PDOException $pdoEx) {
            throw new PersistenceException($pdoEx->getMessage());
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
     * @see makeSelectQuery(I_Persistable $target)
     * @see getAll($targetClassName)
     * @see get(I_Persistable $obj)
     *
     * @return string Of SQL.
     */
    private function getRegistryAndUdiSelectQueryString()
    {
        return 'SELECT ' . self::REGISTRY_ID_COL . ', ' .
        self::DATASET_UDI_COL .
        ' FROM ' . self::REGISTRY_TABLE_NAME . ' ';
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
        $NA = 'N/A';
        $compressedXml = $NA;
        if ($string != null) {
            $compressedXml = preg_replace('/\s+/', ' ', $string);
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
