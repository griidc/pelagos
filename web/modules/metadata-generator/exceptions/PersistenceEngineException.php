<?php
/**
 * PersistenceEngineException
 * Texas A&M Corpus Christi
 * Harte Research Institute
 * Gulf (of Mexico) Research Initiative Information Data Cooperative
 * GRIIDC
 */

namespace Exception;

/**
 * Class PersistenceEngineException
 * @package Exception
 * An exception to be thrown when the underlying
 * storage mechanism encounters an error
 * but the client has no knowledge of the details
 * of the storage engine. Often the message in this
 * exception might come from the database etc.
 */

class PersistenceEngineException extends \Exception
{

    public function __construct($message)
    {
        parent::__construct($message);
    }
}
