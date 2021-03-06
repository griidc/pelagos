<?php
// @codingStandardsIgnoreFile

# Library for simplistic database handle selection
# resolves a given database by name in the centralized
# db.ini file for griidc and returns a connected and
# authenticated database handle, regardless of type
# of database.
#
# Author: Williamson, Dec 2013
#
# Requires: access to centralized db.ini

if (!function_exists('openDB')) {
    function openDB($database = "unspecified", $throwExceptions = false)
    {
        $pelagos_config  = parse_ini_file('/etc/opt/pelagos.ini', true);

        # GRIIDC's databases are all listed in this ini file.
        $configini = parse_ini_file($pelagos_config['paths']['conf'].'/db.ini', true);

        $pdoconnection = null;
        $config = null;

        if (!isset($configini["$database"])) {
            $dMessage  = "DB connection error: The database you specified, ";
            $dMessage .= "<i>$database</i>, could not be found in the GRIIDC ";
            $dMessage .= "database ini file.";
            throw new Exception($dMessage);
            return $pdoconnection;
        } else {
            $config = $configini["$database"];
        }

        if ($config["type"] == 'mysql') {
            # driver used: mysql
            $dbconnstr  = "mysql:host=".$config["host"].';';
            $dbconnstr .= 'port='.$config["port"].';';
            $dbconnstr .= 'dbname='.$config["dbname"].';';
            $dbconnstr .= 'charset=utf8';
            $user       = $config["username"];
            $password   = $config["password"];
            try {
                if ($throwExceptions) {
                    $pdoconnection = new PDO(
                        $dbconnstr,
                        $user,
                        $password,
                        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                              PDO::ATTR_PERSISTENT => false,
                              PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
                    );
                } else {
                    $pdoconnection = new PDO(
                        $dbconnstr,
                        $user,
                        $password,
                        array(PDO::ATTR_PERSISTENT => false,
                              PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
                    );
                }
            } catch (PDOException $e) {
                $dMessage = 'Connection failed: ' . $e->getMessage();
                throw new Exception($dMessage);
            }
        } elseif ($config["type"] == 'postgresql') {
            # driver used: pgsql
            $dbconnstr  = "pgsql:host=".$config["host"].';';
            $dbconnstr .= 'port='.$config["port"].';';
            $dbconnstr .= 'dbname='.$config["dbname"].';';
            $user       = $config["username"];
            $password   = $config["password"];
            try {
                if ($throwExceptions) {
                    $pdoconnection = new PDO(
                        $dbconnstr,
                        $user,
                        $password,
                        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                              PDO::ATTR_PERSISTENT => true,
                              PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
                    );
                } else {
                    $pdoconnection = new PDO(
                        $dbconnstr,
                        $user,
                        $password,
                        array(PDO::ATTR_PERSISTENT => true,
                              PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
                    );
                }
            } catch (PDOException $e) {
                $dMessage = 'Connection failed: ' . $e->getMessage();
                throw new Exception($dMessage);
            }
        } else {
            $dMessage =  "Connection failed: unknown database type specified in ";
            $dMessage .= "the GRIIDC ini file for the <i>$database</i> database";
            throw new Exception($dMessage);
        }
        return $pdoconnection;
    }
}
