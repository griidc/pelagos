<?php

namespace Pelagos\Component\MetadataGenerator;

use \Pelagos\Exception\NotFoundException;

function getLocalEnvironment()
{
    $locals = array();
    $locals['pelagos']['title'] = 'Metadata Generator';
    // load global pelagos config
    $locals['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

    # load Common library from global share
    require_once 'Common.php';

    # check for local config file
    if (file_exists(__DIR__.'/../config.ini')) {
        # merge local config with global config
        $locals['config'] = configMerge($locals['config'], parse_ini_file('config.ini', true));
    }

    # load library info
    $locals['libraries'] = parse_ini_file($locals['config']['paths']['conf'] . '/libraries.ini', true);
    return $locals;
}

/**
 * Using a MetadataXmlFromDB instance, retrieve
 * a metadata information from the database as identified
 * by the dataset UDI provided.
 * The datasetUDI is used to identify a registry id.
 * The registry ID is used to fetch the xml from the metadata table.
 * Return the xml data from the metatdata table.
 * @throws NotFoundException
 * @throws PersistenceException
 * @return XML as string from Metadata in Gomri database
 */

function getMetadataXmlFromGomriDB($datasetUdi)
{
    $mdgu = MetadataXmlFromDB::getInstance();
    return $mdgu->getMetadataXmlForDatasetUdi($datasetUdi);
}

/**
 * Using an instance of XMLDataFile, retrieve metadata xml
 * from a file.
 * Throw PersistenceException
 * if there is a database problem.
 * Throw NotFoundException if the database finds a path
 * but the path is not readable.
 * @param string $datasetUdi
 * @return string xml
 * @throws NotFoundException
 * @throws PersistenceException
 */

function getMetadataXmlFromFile($datasetUdi)
{
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

    $tidy = new \tidy;
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
function legacyGetMetadataXml($udi, $app)
{
    require_once 'DBUtils.php';
    require_once 'datasets.php';
    require_once 'rpis.php';
    $env = getLocalEnvironment();
    $stash = array();
    $GOMRI_DBH = openDB('GOMRI_RW');
    $RIS_DBH = openDB('RIS_RO');
    $datasets = get_identified_datasets($GOMRI_DBH, array("udi=$udi"));

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
        
        date_default_timezone_set('UTC');
        $stash['isoDatetime'] = date('c');

        $app->view()->appendData($stash);
        $xml = $app->view()->render('xml/MI_Metadata.xml');
        return $xml;
    }
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
    require_once 'Common.php';

    # check for local config file
    if (file_exists('config.ini')) {
        # merge local config with global config
        $locals['config'] = configMerge($locals['config'], parse_ini_file('config.ini', true));
    }

    # load library info
    $locals['libraries'] = parse_ini_file($locals['config']['paths']['conf'] . '/libraries.ini', true);
    return $locals;
}
