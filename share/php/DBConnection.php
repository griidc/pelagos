<?php

namespace Pelagos;

# DBConnection Class

class DBConnection
{
    private $pdoconnection;
    
    public function __invoke()
    {
        return $this->pdoconnection;
    }

    public function __construct($database = "unspecified")
    {
        # the following provides drupal_set_message if this is ever
        # to be used outside of drupal, otherwise drupal's implmentation will
        # be used.
        $pelagos_config  = parse_ini_file('/etc/opt/pelagos.ini', true);
        require_once $pelagos_config['paths']['share'].'/php/drupal.php';

        # GRIIDC's databases are all listed in this ini file.
        $configini = parse_ini_file($pelagos_config['paths']['conf'].'/db.ini', true);

        $this->pdoconnection = null;
        $config = null;

        if (!isset($configini["$database"])) {
            $dMessage  = "DB connection error: The database you specified, ";
            $dMessage .= "<i>$database</i>, could not be found in the GRIIDC ";
            $dMessage .= "database ini file.";
            throw new Exception($dMessage);
        } else {
            $config = $configini["$database"];
        }
        
        if ($config["type"] == 'mysql') {
            # driver used: mysql
            $dbconnstr  = "mysql:host=".$config["host"].';';
            $dbconnstr .= 'port='.$config["port"].';';
            $dbconnstr .= 'dbname='.$config["dbname"];
            $user       = $config["username"];
            $password   = $config["password"];

            $this->pdoconnection = new PDO($dbconnstr, $user, $password, array(PDO::ATTR_PERSISTENT => false));
        } elseif ($config["type"] == 'postgresql') {
            # driver used: pgsql
            $dbconnstr  = "pgsql:host=".$config["host"].';';
            $dbconnstr .= 'port='.$config["port"].';';
            $dbconnstr .= 'dbname='.$config["dbname"].';';
            $user       = $config["username"];
            $password   = $config["password"];
            
            $this->pdoconnection = new PDO($dbconnstr, $user, $password, array(PDO::ATTR_PERSISTENT => true));
        } else {
            $dMessage =  "Connection failed: unknown database type specified in ";
            $dMessage .= "the GRIIDC ini file for the <i>$database</i> database";
            throw new Exception($dMessage);
        }
        
        return $this->pdoconnection;
    }
    
    public function executeQuery($query, $parameters)
    {
        $statementHandler = $this->pdoconnection->prepare($query);
        $rc = $statementHandler->execute($parameters);
        if (!$rc) {
            return $statementHandler->errorInfo();
        }
        return $statementHandler->fetchAll();
    }
    
    /**
     * @param string $req : the query on which link the values
     * @param array $array : associative array containing the values ??to bind
     * @param array $typeArray : associative array with the desired value for its corresponding key in $array
     * */
    function bindArrayValue($req, $array, $typeArray = false)
    {
        if (is_object($req) && ($req instanceof PDOStatement)) {
            foreach ($array as $key => $value) {
                if ($typeArray) {
                    $req->bindValue(":$key", $value, $typeArray[$key]);
                } else {
                    if (is_int($value)) {
                        $param = PDO::PARAM_INT;
                    } elseif (is_bool($value)) {
                        $param = PDO::PARAM_BOOL;
                    } elseif (is_null($value)) {
                        $param = PDO::PARAM_NULL;
                    } elseif (is_string($value)) {
                        $param = PDO::PARAM_STR;
                    } else {
                        $param = false;
                    }
                    
                    if ($param) {
                        $req->bindValue(":$key", $value, $param);
                    }
                }
            }
        }
    }
    
    public function __destruct()
    {
        $this->pdoconnection = null;
    }
}
