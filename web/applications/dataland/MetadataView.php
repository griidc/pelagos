<?php


namespace Pelagos\Dataland;

use \PDO as PDO;

/**
 * An encapsulation of access to the Metadata View in the gomri database..
 *
 * This class is used to facilitate accessing the information that is in the postgres
 * view created to serve up the metadata.
 */
class MetadataView
{
    /**
     * Database connection.
     *
     * @var $pconn PDO
     */
    private $pconn = null;
    const TABLENAME = 'metadata_view';
    const KEYCOL = 'registry_id';

    /**
     * Create an MetadataView instance.
     *
     * @param PDO $pdoConnection The database connection provided.
     */
    public function __construct(PDO $pdoConnection)
    {
         $this->pconn = $pdoConnection;
    }

    /**
     * Return the title from metadata for the provided registration id.
     *
     * @param string $registryId The key for the view lookup.
     *
     * @return string The title as found in the metadata.
     */
    public function getTitle($registryId)
    {
        return $this->getValue($registryId, 'title');
    }

    /**
     * Return the abstract from metadata for the provided registration id.
     *
     * @param string $registryId The key for the view lookup.
     *
     * @return string The abstract stored in metadata.
     */
    public function getAbstract($registryId)
    {
        return $this->getValue($registryId, 'abstract');
    }

    /**
     * Return the extent description from metadata for the provided registration id.
     *
     * @param string $registryId The key for the view lookup.
     *
     * @return string The extent description stored in metadata.
     */
    public function getExtentDescription($registryId)
    {
        return $this->getValue($registryId, 'extent_description');
    }

    /**
     * An internal function that retrieves a value from the metadata view.
     *
     * @param string $registryId The key for the view lookup.
     * @param string $columnName The name of the column in the view that contains the target value.
     *
     * @return null|mixed The target value found in the specified column.
     */
    private function getValue($registryId, $columnName)
    {
        $query = 'SELECT ' . $columnName . ' FROM ' . self::TABLENAME . ' WHERE ' . self::KEYCOL . ' = :value';
        try {
            $statement = $this->pconn->prepare($query);
            $statement->bindParam(':value', $registryId, PDO::PARAM_STR);
            $statement->execute();
            $rows = $statement->fetchAll();
            // first row , column one
            return $rows[0][0];
        } catch (\PDOException $e) {
            echo $e->getMessage();
            return null;
        }
    }
}
