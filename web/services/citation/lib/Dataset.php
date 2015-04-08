<?php
/**
 * User: jvh
 * Date: 4/1/15
 * Time: 1:23 PM
 */
//require_once "./UdiValidation.php";
//require_once "./InvalidUdiException.php";


class Dataset
{

    const UDI_REST_TAG = "udi=";

    public function Dataset()
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
        return openDB('GOMRI_RO');;
    }

    /**
     * @param string with a single udi of the form An.xnnn.nnn.nnnn
     * @return json array if success
     * false on nothing found
     */

    public function getRegisteredDataset($udiTarget) // throws InvalidUdiException
    {
        $dbh = $this->getDatabaseConnection();

        $udi =  $this->validate($udiTarget);   // throws InvalidUdiException

        $filter = array(self::UDI_REST_TAG.$udi);
        //  call to pelagos/share/php/datasets.php
        require_once('datasets.php');
        $rds = get_registered_datasets($dbh, $filter);

        $count = count($rds);

        $rds2 = array();  // new array for simplified version to be transformed to json
        if($count == 0) {
            return false;
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
     * @return json array if success
     * false on nothing found
     */

    public function getRegisteredDatasetCitation($udiTarget) // throws InvalidUdiException
    {
            $rds = $this->getRegisteredDataset($udiTarget);
            if ($rds == false) {
                return false;
            }
            $regid = $rds['registry_id'];
            $title = $rds['title'];
            $doi = $rds['doi'];
            $string = "id: " . $regid . ", " . $title;

            if (strlen($doi) > 0) {
                $string .= ", DOI: " . $doi;
            }

            // $url = $rds['url_data'];

            $string .= ", GRIIDC";
            $citation = new \Pelagos\Citation($regid, $string);
            return $citation;
    }

    /**
     * @param $udiString
     * @return the UDI without the REST tag - or anything else
     */
    public function validate($udiString)
    { // throws InvalidUdiException
        require_once './lib/UdiValidation.php';
        $validator = new  UdiValidation();
        $udi = $validator->validate($udiString);   // throws InvalidUdiException
        return $udi;
    }
}
