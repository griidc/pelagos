<?php
/**
 * GomriMetadataPersistence
 * Texas A&M Corpus Christi
 * Harte Research Institute
 * Gulf (of Mexico) Research Initiative Information Data Cooperative
 * GRIIDC
 */

namespace Persistence;
/**
 * Class GomriMetadataPersistence
 * @package Persistence
 * This class converts instances of the class Metadata into the
 * PostGIS/PGSQL representation and vice versa.
 * In the PostGIS representation the geometry is represented in
 * a type specific non-human readable form.
 * In the Metadata object the geometry is in a text format.
 * This class uses the PostGIS function ST_Astext to convert
 * PostGIS internal representation to text
 * and the PostGIS function ST_GeomFromText to convert from Text
 * to internal PostGis geom format.
 */

use \Metadata\Metadata as Metadata;
use \Exception\NotFoundException as NotFoundException;
use \Exception\DuplicateException as DupicateException;
use \Exception\PersistenceEngineException as PersistenceEngineException;
use \Exception\UnsupportedClassException as UnsupportedClassException;
use \PDO as PDO;

class GomriMetadataPersistence implements  I_Persistence
{

    private $dbcon = null;

    //  column names for gomri PGSQL metadata table
    const REGISTRY_ID_COL = "registry_id";
    const METADATA_XML_COL = "metadata_xml";
    const GEOMETRY_COL = "geom";
    const GEOMETRY_COL_ST_ASTEXT = "st_astext"; // this for converting geometry to readable form
    const EXTENT_DESCRIPTION_COL = "extent_description";

    const TABLE_NAME = "public.metadata";


    function __construct()
    {
        $this->dbcon = OpenDB("GOMRI_RW");
        $this->dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * A function required by I_Persistence.
     * Add the provided I_Persistable instance to the store
     * or report why not via Exception.
     * Delegates to addObject
     * @param I_Persistable $obj
     * @return bool
     * @throws DuplicateException
     * @throws UnsupportedClassException
     * @see I_Persistable
     * @see addObject(I_Persistable $obj)
     */
    public function add(I_Persistable $obj)
    {
        if ($this->isClassSupported($obj->getClassName()) == false) {
            throw new UnsupportedClassException("class name: " . $obj->getClassName() . " is not supported for persistence");
        }
        return $this->addObject($obj);
    }

    /**
     * A private helper function for add(I_Persistable $obj)
     * This function enforces duplicates rule.
     * This function stores the object in the GOMRI database
     * @param I_Persistable $obj
     * @return bool
     * @throws DuplicateException
     * @throws UnsupportedClassException
     * @throws PersistenceEngineException
     * @see I_Persistable
     * @see add(I_Persistable $obj)
     */
    private function addObject(I_Persistable $obj) {

        if ($this->isClassSupported($obj->getClassName()) == false){
            throw new UnsupportedClassException("class name: " . $obj->getClassName() . " is not supported for persistence");
        }
        try {
            if ($this->isDuplicateAllowed($obj) == false &&
                $this->isDuplicate($obj) == true
            ){
                throw new DuplicateException("Duplicate Exception - can't add duplicate " . $obj);
            } else {  // either not a duplicate or duplicates are allowed

                $query = $this->makeInsertQuery($obj);
                $statement = $this->dbcon->prepare($query);
                if ($statement->execute()){
                    return true;
                }
            }
        } catch (PDOException $pdoEx) {
            throw new PersistenceEngineException($pdoEx->getMessage());
        }
    }

    /**
     * A delegate of function addObject(I_Persistable $obj)
     * This function returns sthe query needed to store the
     * object in the pgsql database
     *
     * @param I_Persistable $obj
     * @see I_Persistable
     * @see function addObject(I_Persistable $obj)
     * @return string
     */
    private function makeInsertQuery(I_Persistable $obj) {
        $query = "INSERT INTO  ".self::TABLE_NAME." ";


        $colNames = array();
        $values = array();


        if($obj->getMetadataXml() != null) {
            $colNames[] = self::METADATA_XML_COL;
            $values[] = $obj->getMetadataXml();
        }
        if($obj->getGeometry() != null) {
            $colNames[] = self::GEOMETRY_COL;
            $values[] = $this->createTextToGeomClause($obj->getGeometry());
        }
        if($obj->getExtentDescription() != null) {
            $colNames[] = self::EXTENT_DESCRIPTION_COL;
            $values[] = $obj->getExtentDescription();
        }
        if(count($colNames) > 0) {
            $query .= " (  ";
            $lastNdx = count($colNames) - 1;
            for($ndx = 0;$ndx < count($colNames); $ndx++)  {
                $query .= " ".$colNames[$ndx];
                if($ndx < $lastNdx) {
                    $query .= ", ";
                }
            }
            $query .= ") ";
        }
        if(count($values) > 0) {
            $query .= " VALUES ( ";
            $lastNdx = count($values) - 1;
            for($ndx = 0;$ndx < count($values); $ndx++)  {
                $query .= " ".$this->wrapInSingleQuotes($values[$ndx]);
                if($ndx < $lastNdx) {
                    $query .= ", ";
                }
            }
            $query .= ") ";
        }
        return $query;

    }

    /**
     * Change the contents of a stored object. Must be able
     * to fetch it first.
     *
     * Return true on success, throws exception on failure.
     * @param I_Persistable $obj
     * @return bool
     * @throws Exception
     * @throws NotFoundException
     * @throws UnsupportedClassException
     * @throws PersistenceEngineException
     */
    public function modify(I_Persistable $obj) {
        if ($this->isClassSupported($obj->getClassName()) == false){
            throw new UnsupportedClassException("class name: " . $obj->getClassName() . " is not supported for persistence");
        }

        $existingObj = $this->get($obj);
        $query = $this->makeUpdateQuery($obj);
        try {
            $statement = $this->dbcon->prepare($query);
            if ($statement->execute()){
                return true;
            }
        } catch (PDOException $pdoEx) {
            throw new PersistenceEngineException($pdoEx->getMessage());
        } catch (NotFoundException $ex) {
            throw $ex;
        }
    }
    /**
     * A delegate of function modify(I_Persistable $obj)
     * This function returns sthe query needed to modify the
     * object in the pgsql database
     *
     * @param I_Persistable $obj
     * @see I_Persistable
     * @see function modify(I_Persistable $obj)
     * @return string
     * */
    private function makeUpdateQuery(I_Persistable $target) {
        if ($this->isClassSupported($target->getClassName()) == false) {
            throw new UnsupportedClassException("class name: " . $target->getClassName() . " is not supported for persistence");
        }
        if(strcmp($target->getClassName(),Metadata::getClassName()) == 0) {
            $query = "UPDATE  ".self::TABLE_NAME." ";

            $clauseElements = $this->createUpdateOrSelectQueryClauseElements($target);
            if(count($clauseElements) > 0) {
                $query .= " SET ";
                $lastNdx = count($clauseElements) - 1;
                for($ndx = 0;$ndx < count($clauseElements); $ndx++)  {
                    $query .= " ".$clauseElements[$ndx];
                    if($ndx < $lastNdx) {
                        $query .= ", ";
                    }
                }
            }
            $query .= " WHERE ".self::REGISTRY_ID_COL." = ".$target->getKey();
            return $query;
        }
    }

    /**
     * A function to remove an object from the database.
     * The object must exist.
     * Return true on success, throws exception on failure.
     * @param I_Persistable $obj
     * @return bool
     * @throws Exception
     * @throws NotFoundException
     * @throws UnsupportedClassException
     * @throws PersistenceEngineException
     */
    public function delete(I_Persistable $obj)
    {
        if ($this->isClassSupported($obj->getClassName()) == false) {
            throw new UnsupportedClassException("class name: " . $obj->getClassName() . " is not supported for persistence");
        }
        //  does the object exist - if not it can't be deleted
        try {
            $found = $this->get($obj);
            $targetKey = $found->getKey();
            $query = "DELETE  FROM ".self::TABLE_NAME." WHERE ".self::REGISTRY_ID_COL. " = ".$targetKey;
            $statement = $this->dbcon->prepare($query);
            if ($statement->execute()) {
                return true;
            }
        } catch (PDOException $pdoEx) {
            throw new PersistenceEngineException($pdoEx->getMessage());
        } catch (NotFoundException $ex) {  // not found - can't delete
            throw $ex;
        }
    }

    /**
     * Search for all stored objects that
     * "match" the target. Return a set of them
     * as an associative array.
     * @param I_Persistable $target
     * @return array
     * @throws NotFoundException
     * @throws UnsupportedClassException
     * @throws PersistenceEngineException
     */
    public function find(I_Persistable $target)
    {
        $query = $this->makeSelectQuery($target);
        $matchingSet = array();
        $statement = $this->dbcon->prepare($query);
        $metadata = null;
        try {
            if ($statement->execute()){
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {

                    $metadata = $this->createMetadataFromPgData($row);
                    $matchingSet[$metadata->getKey()] = $metadata;
                }
            }
            if (count($matchingSet) > 0){
                return $matchingSet;
            }
            throw new NotFoundException("No object found matching " . $target);
        } catch (PDOException $pdoEx) {
            throw new PersistenceEngineException($pdoEx->getMessage());
        }
    }
    /**
     * A delegate of function find(I_Persistable $target)
     * This function returns the query needed to find the
     * matching objects in the pgsql database
     *
     * @param I_Persistable $obj
     * @see I_Persistable
     * @see function find(I_Persistable $target)
     * @return string
     * */
    private function makeSelectQuery(I_Persistable $target) {
        if ($this->isClassSupported($target->getClassName()) == false) {
            throw new UnsupportedClassException("class name: " . $target->getClassName() . " is not supported for persistence");
        }
        if(strcmp($target->getClassName(),Metadata::getClassName()) == 0) {
            $query = $this->getAllColumnSelectQueryString();

            $clauseElements = $this->createUpdateOrSelectQueryClauseElements($target);
            if(count($clauseElements) > 0) {
                $query .= " WHERE ";
                $lastNdx = count($clauseElements) - 1;
                for($ndx = 0;$ndx < count($clauseElements); $ndx++)  {
                    $query .= " ".$clauseElements[$ndx];
                    if($ndx < $lastNdx) {
                        $query .= " AND ";
                    }
                }
            }
            return $query;
        }
    }

    private function createUpdateOrSelectQueryClauseElements(I_Persistable $target) {

        $clauseElements = array();

        if($target->getRegistryId() != null) {
            $clauseElements[] = self::REGISTRY_ID_COL . " = " . $this->wrapInSingleQuotes($target->getRegistryId());
        }
        if($target->getMetadataXml() != null) {
            $clauseElements[] = self::METADATA_XML_COL . " = " .$this->wrapInSingleQuotes( $target->getMetadataXml());
        }
        if($target->getGeometry() != null) {
            $clauseElements[] = self::GEOMETRY_COL . " = " . $this->wrapInSingleQuotes($target->getGeometry());
        }
        if($target->getExtentDescription() != null) {
            $clauseElements[] = self::EXTENT_DESCRIPTION_COL . " = " . $this->wrapInSingleQuotes($target->getExtentDescription());
        }
        return $clauseElements;
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
    private function getAllColumnSelectQueryString() {
        return  "SELECT ".self::REGISTRY_ID_COL. ", ".
            self::METADATA_XML_COL.", ".
            $this->createGeomToTextClause(). ", ".
            self::EXTENT_DESCRIPTION_COL .
            " FROM ".self::TABLE_NAME." ";
    }
    /**
     * Return all the objects of the type identified
     * by class name.
     * Returns an array of objects of type
     * @param $targetClassName
     * @return array
     * @throws NotFoundException
     * @throws UnsupportedClassException
     * @throws PersistenceEngineException
     */
    public function getAll($targetClassName) {
        if ($this->isClassSupported($targetClassName) == false){
            throw new UnsupportedClassException("class name: " . $targetClassName . " is not supported for persistence");
        }
        $all = array();
        $query = $this->getAllColumnSelectQueryString();

        $statement = $this->dbcon->prepare($query);
        $metadata = null;
        try {
            if ($statement->execute()){
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $metadata = $this->createMetadataFromPgData($row);
                    // put the object in the array to return
                    $all[$metadata->getKey()] = $metadata;
                }
                if (count($all) > 0){
                    return $all;
                } else {
                    throw new NotFoundException("No objects of type  " . $targetClassName . " found in " . $this->getName());
                }
            }
        } catch (PDOException $pdoEx) {
            throw new PersistenceEngineException($pdoEx->getMessage());
        }
    }

    /**
     * A function to create an instance of Metadata from one row of
     * a PostGres fetch. This function is shared by two or more functions.
     * @param $row
     * @return Metadata
     * @see getAll($targetClassName)
     * @see find(I_Persistable $target)
     * @see get(I_Persistable $obj)
     */
    private function createMetadataFromPgData($row) {

        $metadata = new Metadata();
        $metadata->setRegistryId($row[self::REGISTRY_ID_COL]);
        $metadata->setMetadataXml($row[self::METADATA_XML_COL]);
        $metadata->setGeometry($row[self::GEOMETRY_COL_ST_ASTEXT]);
        $metadata->setExtentDescription($row[self::EXTENT_DESCRIPTION_COL]);
        return $metadata;
    }

    /**
     * Fetch the object based on it's unique id.
     * @param I_Persistable $obj
     * @return the Metadata instance if it exists in the store
     * @throws NotFoundException of the object is not found
     * @throws PersistenceEngineException
     */
    public function get(I_Persistable $obj) {

        $BR = "<br>";
        $targetKey = $obj->getKey();
        $query = $this->getAllColumnSelectQueryString() . " WHERE " . self::REGISTRY_ID_COL . " = " .$this->wrapInSingleQuotes($targetKey) . " LIMIT 1";
        $statement = $this->dbcon->prepare($query);
        $metadata = null;
        try {
            if ($statement->execute()) {
                if ($row = $statement->fetch(PDO::FETCH_ASSOC)) { // if true
                    $metadata = $this->createMetadataFromPgData($row);
                    return $metadata;
                } // else it is false - not found
                throw new NotFoundException("No object found with key " . $targetKey);
            }
        } catch (PDOException $pdoEx) {
            throw new PersistenceEngineException($pdoEx->getMessage());
        }
    }

    /**
     * Return the name of this storage system.
     * In this case a postgress database named gomri
     * @return string
     */
    public function getName() {
        return  "POSTRGRES:GOMRI";
    }

    /**
     * @param I_Persistable $obj
     * @return true if objects of this type can
     * have duplicates in the store
     */
    public function isDuplicateAllowed(I_Persistable $obj) {
        // if obj is of type Metadata - no duplicates are allowed
        if (strcmp($obj->getClassName(), Metadata::getClassName()) == 0){
            return false;
        }
        return true;
    }

    /**
     * @throws PersistenceEngineException
     */
    private function isDuplicate(I_Persistable $target) {

        try {
            $allOfThisType = $this->getAll($target->getClassName());
            foreach ($allOfThisType as $item) {
                if ($item->isEqual($target)){
                    return true;
                }
            }
            return false;
        } catch (PDOException $pdoEx) {
            throw new PersistenceEngineException($pdoEx->getMessage());
        }
    }
    /**
     * Return true if the class name provided
     * matches a class name that is supported
     * by this persistence mechanism
     * @param $className
     * @return bool
     */
    private function isClassSupported($className) {

        if(strcmp($className,Metadata::getClassName()) == 0) {
            return true;
        }
        return false;
    }

    private function wrapInSingleQuotes($s) {
        return "'".$s."'";
    }

    /**
     * use PGSQL  ST_ASTEXT function to convert a
     * geom type to text
     * Used for reading from the database
     */
    private function createGeomToTextClause() {
        return " ST_Astext(". self::GEOMETRY_COL . ") ";
    }

    /**
     * use the PGSQL  ST_GeomFromText function to convert a
     * text representaton to GEOM
     * Used for writing to the database
     */
    private function createTextToGeomClause($geomWKT) {
        return " ST_GeomFromText(".$this->wrapInSingleQuotes($geomWKT).") ";
    }
}