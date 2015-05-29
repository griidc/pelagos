<?php

$GLOBALS['pelagos']['title'] = 'Metadata Generator';

# load global pelagos config
$GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

# load Common library from global share
require_once($GLOBALS['config']['paths']['share'].'/php/Common.php');

# check for local config file
if (file_exists('config.ini')) {
    # merge local config with global config
    $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file('config.ini', true));
}

# load library info
$GLOBALS['libraries'] = parse_ini_file($GLOBALS['config']['paths']['conf'] . '/libraries.ini', true);

# load Slim2
require_once $GLOBALS['libraries']['Slim2']['include'];
# register Slim autoloader
\Slim\Slim::registerAutoloader();
# load Twig Slim-View
require_once $GLOBALS['libraries']['Slim-Views']['include_Twig'];
# load Twig
require_once 'Twig/Autoloader.php';

# add pelagos/share/php to the include path
set_include_path(get_include_path() . PATH_SEPARATOR . $GLOBALS['config']['paths']['share'] . '/php');

require_once 'rpis.php';
require_once 'datasets.php';
require_once 'codelists.php';
require_once 'drupal.php';
require_once '../../../share/php/db-utils.lib.php';
require_once "./exceptions/DuplicateException.php";
require_once "./exceptions/NotFoundException.php";
require_once "./exceptions/PersistenceEngineException.php";
require_once "./lib/MetadataXmlFromDB.php";
require_once "./lib/Validator.php";
require_once "./lib/XMLDataFile.php";

use \Exception\NotFoundException as NotFoundException;
use \Exception\DuplicateException as DuplicateException;
use \Exception\PersistenceEngineException as PersistenceEngineException;
use \MetadataGenerator\MetadataXmlFromDB as MetadataXmlFromDB;
use \MetadataGenerator\XMLDataFile as XMLDataFile;

# initialize Slim
$app = new \Slim\Slim(array('view' => new \Slim\Views\Twig()));

$app->get('/', function () use ($app) {
    echo <<<EOT
<p>Usage: $_SERVER[SCRIPT_NAME]/\$udi</p>
<p>Example: <a href="$_SERVER[SCRIPT_NAME]/R1.x134.114:0008">/metadata-generator/R1.x134.114:0008</a></p>
EOT;
});

/**
 * Using a MetadataXmlFromDB instance, retrieve
 * a metadata information from the database as identified
 * by the dataset UDI provided.
 * The datasetUDI is used to identify a registry id.
 * The registry ID is used to fetch the xml from the metadata table.
 * Return the xml data from the metatdata table.
  * @throws NotFoundException
 * @throws PersistenceEngineException
 * @return XML as string from Metadata in Gomri database
 */

function getMetadataXmlFromGomriDB($datasetUdi) {
    $mdgu = new MetadataXmlFromDB();
    return $mdgu->getMetadataXmlForDatasetUdi($datasetUdi);
}

/**
 * Using an instance of XMLDataFile, retrieve metadata xml
 * from a file.
 * Throw PersistenceEngineException
 * if there is a database problem.
 * Throw NotFoundException if the database finds a path
 * but the path is not readable.
 * @param $datasetUdi
 * @return string xml
 * @throws NotFoundException
 * @throws PersistenceEngineException
 */

function getMetadataXmlFromFile($datasetUdi)
{
    $xmldf = new XMLDataFile();
    $xml = $xmldf->getXML($datasetUdi);
    return $xml;
}

/**
 * This is the first method implemented for returning xml.
 * Bit of a black box but I know it does not rely on xml
 * stored in either the database or a file. Call it when the
 * first two methods fail.
 * @param $udi
 * @param $app
 * @return tidy
 * @throws NotFoundException
 */
function legacyGetMetadataXml($udi, $app)
{
    $stash = array();
    $GOMRI_DBH = OpenDB('GOMRI_RO');
    $RIS_DBH = OpenDB('RIS_RO');
    $datasets = get_identified_datasets($GOMRI_DBH, array("udi=$udi"));

    if (count($datasets) > 0) {
        $stash['dataset'] = $datasets[0];
        $stash['dataset']['url'] = "https://data.gulfresearchinitiative.org/data/$udi";
        if (array_key_exists('dataset_download_size', $stash['dataset'])) {
            $size_bytes = $stash['dataset']['dataset_download_size'] / 1048576;
            if ($size_bytes >= 10) $precision = 0;
            else {
                for ($precision = 1; $precision < 6; $precision++) {
                    if ($size_bytes > pow(10, -$precision)) break;
                }
            }
            $stash['dataset']['size'] = round($size_bytes, $precision);
        }

        if (array_key_exists('primary_poc', $stash['dataset'])) {
            $people = getPeopleDetails($RIS_DBH, array('peopleId=' . $stash['dataset']['primary_poc']));
            if (count($people)) {
                $stash['RP']['PPOC'] = $people[0];
                $stash['RP']['PPOC']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['pointOfContact'];
            }
        }

        if (array_key_exists('project_id', $stash['dataset'])) {
            $people = getPeopleDetails($RIS_DBH, array('projectId=' . $stash['dataset']['project_id'], 'RoleId=3'));
            if (count($people)) {
                $stash['RP']['DM'] = $people[0];
                $stash['RP']['DM']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['pointOfContact'];
            }
        }

        $stash['RP']['DIST'] = $GLOBALS['config']['Distributor'];
        $stash['RP']['DIST']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['distributor'];

        //  $stash['dataset']['metadata_filename'] = preg_replace('/:/','-',$udi) . '-metadata.xml';

        $app->view()->appendData($stash);
        $xml = $app->view()->render('xml/MI_Metadata.xml');
        return $xml;

    }
    throw new NotFoundException("C-3 UDI: $udi not found.");
}
$app->get('/DB/:udi', function ($udi) use ($app) {

    $BR = "<BR>";
    $metadataXml = null;
   // $whichCaseSolution = "Cas 4 - No UDI Metadata found";
    //  try the database for stored xml first
    try {
        $metadataXml = getMetadataXmlFromGomriDB($udi);
        finishAllSuccessfulCases($udi,$app,$metadataXml);
        //$whichCaseSolution = "Data found with Case 1 method getMetadataXmlFromGomriDB ";
    } catch (NotFoundException $ex) {
        //drupal_set_message($ex->getMessage());
        //
        //  Case One did not get XML. No XML found in the database.
        //  Try retrieving from a file referenced in the database

        try {
            $metadataXml = getMetadataXmlFromFile($udi);
            finishAllSuccessfulCases($udi,$app,$metadataXml);
            //echo $metadataXml;
            //$whichCaseSolution = " Data found with Case 2 method getMetadataXmlFromFile ";
            exit;
        } catch (NotFoundException $ex) {
            try {
                $metadataXml = legacyGetMetadataXml($udi, $app);
                finishAllSuccessfulCases($udi,$app,$metadataXml);
                //$whichCaseSolution = " Data found with Case 3 method legacyGetMetadataXml ";
                exit;
            } catch (NotFoundException $ex) {
                // echo $BR."Nothing worked - no XML found - Now What?";
                header("status: 204");
                header("HTTP/1.0 204 No Response");
            }
        } catch (PersistenceEngineException $ex) {
            //drupal_set_message($ex->getMessage());
            echo $ex->getMessage();
        }

    } catch (PersistenceEngineException $ex) {
        //drupal_set_message($ex->getMessage());
        echo $ex->getMessage();
    }
    exit;
});
function finishAllSuccessfulCases($udi,$app,$metadataXml) {
    //  in all cases
    $metadataXmlFileName = preg_replace('/:/','-',$udi) . '-metadata.xml';
    header('Content-Type: text/xml');
    if (!preg_match('/^f$|^false$|^n$|^no$/i',$app->request()->get('download'))) {
        header('Content-Disposition: attachment; filename=' . $metadataXmlFileName);
    }
    $tidy_config = array('indent'        => true,
        'indent-spaces' => 4,
        'input-xml'     => true,
        'output-xml'    => true,
        'wrap'          => 0);

    $tidy = new tidy;
    $tidy->parseString($metadataXml, $tidy_config, 'utf8');
    $tidy->cleanRepair();

    echo $tidy;
}
$app->get('/:udi', function ($udi) use ($app) {
    $BR = "<BR>";
    $stash = array();
    $GOMRI_DBH = OpenDB('GOMRI_RO');
    $RIS_DBH = OpenDB('RIS_RO');
    $datasets = get_identified_datasets($GOMRI_DBH,array("udi=$udi"));

    if (count($datasets) > 0) {
        $stash['dataset'] = $datasets[0];
        $stash['dataset']['url'] = "https://data.gulfresearchinitiative.org/data/$udi";
        if (array_key_exists('dataset_download_size',$stash['dataset'])) {
            $size_bytes = $stash['dataset']['dataset_download_size'] / 1048576;
            if ($size_bytes >= 10) $precision = 0;
            else {
                for ($precision = 1; $precision < 6; $precision++) {
                    if ($size_bytes > pow(10,-$precision)) break;
                }
            }
            $stash['dataset']['size'] = round($size_bytes,$precision);
        }

        if (array_key_exists('primary_poc',$stash['dataset'])) {
            $people = getPeopleDetails($RIS_DBH,array('peopleId='.$stash['dataset']['primary_poc']));
            if (count($people)) {
                $stash['RP']['PPOC'] = $people[0];
                $stash['RP']['PPOC']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['pointOfContact'];
            }
        }
    
        if (array_key_exists('project_id',$stash['dataset'])) {
            $people = getPeopleDetails($RIS_DBH,array('projectId='.$stash['dataset']['project_id'],'RoleId=3'));
            if (count($people)) {
                $stash['RP']['DM'] = $people[0];
                $stash['RP']['DM']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['pointOfContact'];
            }
        }

        $stash['RP']['DIST'] = $GLOBALS['config']['Distributor'];
        $stash['RP']['DIST']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['distributor'];

        $stash['dataset']['metadata_filename'] = preg_replace('/:/','-',$udi) . '-metadata.xml';

        header('Content-Type: text/xml');
        if (!preg_match('/^f$|^false$|^n$|^no$/i',$app->request()->get('download'))) {
            header('Content-Disposition: attachment; filename=' . $stash['dataset']['metadata_filename']);
        }

        $app->view()->appendData($stash);
        $xml = $app->view()->render('xml/MI_Metadata.xml');
        echo $BR.$xml;

        $tidy_config = array('indent'        => true,
                             'indent-spaces' => 4,
                             'input-xml'     => true,
                             'output-xml'    => true,
                             'wrap'          => 0);

        $tidy = new tidy;
        $tidy->parseString($xml, $tidy_config, 'utf8');
        $tidy->cleanRepair();

        echo $tidy;
        exit;
    }
    else {
        echo $BR."JOE Holland says NO XML FOUND";
        drupal_set_message("UDI: $udi not found.",'error');
    }
});

$app->run();
