<?php


function getLocalEnvironment()
{
    $locals = array();
    $locals['pelagos']['title'] = 'Metadata Generator';
    // load global pelagos config
    $locals['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

    # load Common library from global share
    require_once(__DIR__ . '/../../../share/php/Common.php');

    # check for local config file
    if (file_exists('config.ini')) {
        # merge local config with global config
        $locals['config'] = configMerge($locals['config'], parse_ini_file('config.ini', true));
    }

    # load library info
    $locals['libraries'] = parse_ini_file($locals['config']['paths']['conf'] . '/libraries.ini', true);
    return $locals;
}


$env = getLocalEnvironment();

# load Slim2
require_once $env['libraries']['Slim2']['include'];
# register Slim autoloader
\Slim\Slim::registerAutoloader();
# load Twig Slim-View
require_once $env['libraries']['Slim-Views']['include_Twig'];
# load Twig
require_once 'Twig/Autoloader.php';

# add pelagos/share/php to the include path
set_include_path(get_include_path() . PATH_SEPARATOR . $env['config']['paths']['share'] . '/php');



require_once 'codelists.php';
require_once 'drupal.php';

use \Exception\NotFoundException as NotFoundException;
use \Exception\PersistenceEngineException as PersistenceEngineException;
use \Exception\InvalidXmlException as InvalidXmlException;
use \MetadataGenerator\MetadataXmlFromDB as MetadataXmlFromDB;
use \MetadataGenerator\XMLDataFile as XMLDataFile;
use \MetadataGenerator\MetadataLogger as MetadataLogger;

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

function getMetadataXmlFromGomriDB($datasetUdi)
{
    require_once "./lib/MetadataXmlFromDB.php";
    $mdgu = MetadataXmlFromDB::getInstance();
    return $mdgu->getMetadataXmlForDatasetUdi($datasetUdi);
}

/**
 * Using an instance of XMLDataFile, retrieve metadata xml
 * from a file.
 * Throw PersistenceEngineException
 * if there is a database problem.
 * Throw NotFoundException if the database finds a path
 * but the path is not readable.
 * @param string $datasetUdi
 * @return string xml
 * @throws NotFoundException
 * @throws PersistenceEngineException
 */

function getMetadataXmlFromFile($datasetUdi)
{
    require_once "./lib/XMLDataFile.php";
    $xmldf = XMLDataFile::getInstance();
    $xml = $xmldf->getXML($datasetUdi);
    return $xml;
}

function finishAllSuccessfulCases($udi, $app, $metadataXml)
{
    //  in all cases
    $metadataXmlFileName = preg_replace('/:/', '-', $udi) . '-metadata.xml';
    header('Content-Type: text/xml');
    if (!preg_match('/^f$|^false$|^n$|^no$/i', $app->request()->get('download'))) {
        header('Content-Disposition: attachment; filename=' . $metadataXmlFileName);
    }
    $tidy_config = array('indent' => true,
        'indent-spaces' => 4,
        'input-xml' => true,
        'output-xml' => true,
        'wrap' => 0);

    $tidy = new tidy;
    $tidy->parseString($metadataXml, $tidy_config, 'utf8');
    $tidy->cleanRepair();
    echo $tidy;
}

/**
 * This is the first method implemented for returning xml.
 * Bit of a black box but I know it does not rely on xml
 * stored in either the database or a file. Call it when the
 * first two methods fail.
 * @param string $udi
 * @param slim $app
 * @param MetadataLogger $logger
 * @return tidy
 * @throws NotFoundException
 */
function legacyGetMetadataXml($udi, $app, MetadataLogger $logger)
{
    require_once "./exceptions/NotFoundException.php";
    require_once "./lib/MetadataLogger.php";
    require_once '../../../share/php/db-utils.lib.php';
    require_once 'datasets.php';
    require_once 'rpis.php';
    $env = getLocalEnvironment();
    $stash = array();
    $GOMRI_DBH = OpenDB('GOMRI_RW');
    $RIS_DBH = OpenDB('RIS_RO');
    $logger->write("Metadata Generator - starting legacyGetMetadataXml() udi: " . $udi);
    $datasets = get_identified_datasets($GOMRI_DBH, array("udi=$udi"));

    $logger->write("Metadata Generator - legacyGetMetadataXml() datasets count: " . count($datasets));
    if (count($datasets) > 0) {
        $stash['dataset'] = $datasets[0];
        $stash['dataset']['url'] = "https://data.gulfresearchinitiative.org/data/$udi";
        if (array_key_exists('dataset_download_size', $stash['dataset'])) {
            $size_bytes = $stash['dataset']['dataset_download_size'] / 1048576;
            if ($size_bytes >= 10) {
                $precision = 0;
            } else {
                for ($precision = 1; $precision < 6; $precision++) {
                    if ($size_bytes > pow(10, -$precision)) {
                        break;
                    }
                }
            }
            $stash['dataset']['size'] = round($size_bytes, $precision);
        }

        if (array_key_exists('primary_poc', $stash['dataset'])) {
            $people = getPeopleDetails($RIS_DBH, array('peopleId=' . $stash['dataset']['primary_poc']));
            if (count($people)) {
                $stash['RP']['PPOC'] = $people[0];
                // $stash['RP']['PPOC']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['pointOfContact'];

                $stash['RP']['PPOC']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['pointOfContact'];
            }
        }

        if (array_key_exists('project_id', $stash['dataset'])) {

            $people = getPeopleDetails($RIS_DBH, array('projectId=' . $stash['dataset']['project_id'], 'RoleId=3'));

            if (count($people)) {
                $stash['RP']['DM'] = $people[0];
                $stash['RP']['DM']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['pointOfContact'];  // GLOBALS
            }
        }

        $stash['RP']['DIST'] = $env['config']['Distributor'];
        $stash['RP']['DIST']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['distributor'];

        $app->view()->appendData($stash);
        $xml = $app->view()->render('xml/MI_Metadata.xml');
        $logger->write("Metadata Generator - legacyGetMetadataXml() returning XML");
        return $xml;
    }
    $logger->write("Metadata Generator - legacyGetMetadataXml() Throw NotFoundException ");
    throw new NotFoundException("C-3 UDI: $udi not found.");
}

function getLocals()
{
    global $BR;
    $locals = array();

    $locals['pelagos']['title'] = 'Metadata Generator';
    # load global pelagos config
    $locals['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);
    $locals['RP']['PPOC']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['pointOfContact'];
    $locals['RP']['DM']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['pointOfContact'];
    $locals['RP']['DIST'] = $GLOBALS['config']['Distributor'];
    $locals['RP']['DIST']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['distributor'];


    # load Common library from global share
    require_once($locals['config']['paths']['share'] . '/php/Common.php');

    # check for local config file
    if (file_exists('config.ini')) {
        # merge local config with global config
        $locals['config'] = configMerge($locals['config'], parse_ini_file('config.ini', true));
    }

    # load library info
    $locals['libraries'] = parse_ini_file($locals['config']['paths']['conf'] . '/libraries.ini', true);
    return $locals;
}

# initialize Slim
$app = new \Slim\Slim(array('view' => new \Slim\Views\Twig()));

$app->get(
    '/',
    function () use ($app) {
            echo <<<EOT
<p>Usage: $_SERVER[SCRIPT_NAME]/\$udi</p>
<p>Example: <a href="$_SERVER[SCRIPT_NAME]/R1.x134.114:0008">/metadata-generator/R1.x134.114:0008</a></p>
EOT;
    }
);

/**
 * New entry point. Same url syntax as legacy route.
 * Return XML from storage.
 * 1st - search the Database for approved XML.
 * 2nd - failing to find 1st, look for stored file
 *       referenced in the database.
 * 3rd - failing 1st and 2nd - return a blank dataset template
 */
$app->get(
    '/:udi',
    function ($udi) use ($app) {


        require_once "./exceptions/InvalidXmlException.php";
        require_once "./exceptions/NotFoundException.php";
        require_once "./exceptions/PersistenceEngineException.php";
        require_once "./lib/MetadataLogger.php";
        $trimUdi = trim($udi);

        $logger = new MetadataLogger("indexPHP", $trimUdi);
        $logger->setOff();
        $env = getLocalEnvironment();


        $metadataXml = null;
        $logger->write("Metadata Generator starting  udi: " . $trimUdi);

        //  try the database for stored xml first - CASE 1
        try {
            $logger->write("Metadata Generator - calling getMetadataXmlFromGomriDB() udi: " . $trimUdi);
            $metadataXml = getMetadataXmlFromGomriDB($trimUdi);
            $logger->write("Metadata Generator - back from  getMetadataXmlFromGomriDB() udi: " . $trimUdi);
            finishAllSuccessfulCases($trimUdi, $app, $metadataXml);
            $logger->write("Data found with Case 1 method getMetadataXmlFromGomriDB ");
            //$whichCaseSolution = "Data found with Case 1 method getMetadataXmlFromGomriDB ";
        } catch (NotFoundException $ex) { // Case 1 Not Found Catch
            $logger->write("Metadata Generator - case 1 Not Found Exception udi: " . $trimUdi);
            //  CASE 2
            //  No XML found in the database.
            //  Try retrieving from a file referenced in the database

            try {

                $logger->write("Metadata Generator - calling getMetadataXmlFromFile() udi: " . $trimUdi);
                $metadataXml = getMetadataXmlFromFile($trimUdi);
                finishAllSuccessfulCases($trimUdi, $app, $metadataXml);
                $logger->write("Data found with Case 2 method getMetadataXmlFromFile ");
                exit;
            } catch (InvalidXmlException $ex) {
                $logger->write(
                    "Metadata Generator after getMetadataXmlFromFile caught - InvalidXmlException udi: " . $trimUdi
                );
                header("status: 204");
                header("HTTP/1.0 204 No Response");
                exit;
            } catch (PersistenceEngineException $ex) {
                $logger->write(
                    "Metadata Generator after getMetadataXmlFromFile caught - PersistenceEngineException udi: " .
                    $trimUdi
                );
                header("status: 500");
                header("HTTP/1.0 505 Internal Server Error");
                exit;
            } catch (NotFoundException $ex) { // Case 2 Not Found Catch
                $logger->write("Metadata Generator - case 2 Not Found Exception udi: " . $trimUdi);
                try {
                    $logger->write("Metadata Generator - calling legacyGetMetadataXml() udi: " . $trimUdi);
                    // CASE 3 -
                    $metadataXml = legacyGetMetadataXml($trimUdi, $app, $logger);
                    finishAllSuccessfulCases($trimUdi, $app, $metadataXml);
                    //$whichCaseSolution = " Data found with Case 3 method legacyGetMetadataXml ";
                    $logger->write("Data found with Case 3 method legacyGetMetadataXml ");
                    exit;
                } catch (NotFoundException $ex) { // Case 3 Not Found Catch
                    $logger->write("Metadata Generator - case 3 Not Found Exception udi: " . $trimUdi);
                    header("status: 404");
                    header("HTTP/1.0 404 Not Found");
                    exit;
                }
            }
        } catch (PersistenceEngineException $ex) {
            $logger->write("Metadata Generator -PersistenceEngineException udi: " . $trimUdi);
            header("status: 500");
            header("HTTP/1.0 505 Internal Server Error");
            exit;
        }
        exit;
    }
);


$app->run();
