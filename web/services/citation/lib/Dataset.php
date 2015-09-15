<?php
// @codingStandardsIgnoreFile

namespace Citation;

/**
 * This class that provides functionality to
 * fetch the dataset registered to the UDI.
 * It validates its udi string parameter and throws
 * an InvalidUdiException if the validation process fails.
 */
class Dataset
{
    const UDI_REST_TAG = "udi=";

    public function __construct()
    {
        // load global pelagos config
        $GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

        # load Common library from global share
        require_once($GLOBALS['config']['paths']['share'] . '/php/Common.php');

        # check for local config file
        if (file_exists('config.ini')) {
            # merge local config with global config
            $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file('config.ini', true));
        }

        # load library info
        $GLOBALS['libraries'] = parse_ini_file($GLOBALS['config']['paths']['conf'] . '/libraries.ini', true);

        # load database connection info
        $GLOBALS['db'] = parse_ini_file($GLOBALS['config']['paths']['conf'] . '/db.ini', true);
    }

    /**
     * Open the PDO connection and return it
     * @return the PDO connection
     */
    private function getDatabaseConnection()
    {
        require_once('DBUtils.php');
        return openDB('GOMRI_RO');
    }

    /**
     * @param string with a single udi of the form An.xnnn.nnn.nnnn
     * @return associative array with all columns in the table if success or false if record not found
     * @throws InvalidUdiException
     * @see InvalidUdiException
     */

    public function getRegisteredDataset($udiTarget) // throws InvalidUdiException
    {
        require_once './lib/NoRegisteredDatasetException.php';
        $dbh = $this->getDatabaseConnection();

        $udi = $this->validate($udiTarget);   // throws InvalidUdiException

        $filter = array(self::UDI_REST_TAG . $udi);
        //  call to pelagos/share/php/datasets.php
        require_once('datasets.php');
        $rds = get_registered_datasets($dbh, $filter);

        $count = count($rds);

        $rds2 = array();  // new array for simplified version to be transformed to json
        if ($count == 0) {
            throw new \Citation\NoRegisteredDatasetException("No dataset registered for UDI: ".$udi);
        }
        $rds = $rds[0];  // there can only be one
        $keys = array_keys($rds);

        foreach ($keys as $key) {
            if (!is_numeric($key)) {
                $rds2[$key] = $rds[$key];  // add the element to the new array
            }
        }
        return $rds2;
    }

    /**
     * @param string with a single udi of the form An.xnnn.nnn.nnnn
     * @return Citation object or false if no record found
     * @throws InvalidUdiException
     * @see InvalidUdiException
     */

    public function getRegisteredDatasetCitation($udiTarget) // throws InvalidUdiException, NoRegisteredDatasetException
    {
        $rds = $this->getRegisteredDataset($udiTarget);
        return $this->createCitation($rds);
    }

    /**
     * create a Citation from information found in the registered dataset.
     * Note that the doi column may contain a url or just the doi identifier
     */
    private function createCitation($ds)
    {
        if ($ds == false) {
            return false;
        }
        $author = $ds["dataset_originator"];
        $year = $ds["year"];
        $title = $ds['title'];
        $doi = $ds['doi'];
        $udi = $ds["udi"];
        $regId = $ds["registry_id"];

        // The doi could include a url - check for it
        if ($doi && strlen($doi) > 0) {
            // does the doi contain the string http
            $pos = strpos($doi, "http");
            if ($pos === false) { // make a url from the doi
                $url = "http://dx.doi.org/" . $doi;
            } else {  // stored doi is a url
                $url = $doi;
            }
        } else {
            $url = "http://data.gulfresearchinitiative.org/data/" . $udi;
        }
        $citationString = $author . " (" . $year . "). " . $title ." (".$udi.") ".
                   "[Data files] Available from " . $url;

        $citation = new \Pelagos\Entity\Citation($regId, $citationString);
        return $citation;
    }


    /**
     * Test the string for conformance to the
     * known qualities of a UDI using the UdiValidation
     * @param $udiString
     * @return the UDI without the REST tag - or anything else
     * @throws InvalidUdiException
     * @see UdiValidation
     * @see InvalidUdiException
     */
    public function validate($udiString)
    {
        require_once './lib/UdiValidation.php';
        $validator = new  \Citation\UdiValidation();
        $s = trim($udiString);  // white space is so pesky
        $udi = $validator->validate($s);   // throws InvalidUdiException
        return $udi;
    }
}
