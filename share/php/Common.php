<?php

if (!function_exists('configMerge')) {
    function configMerge($config_ini, $custom_ini)
    {
        foreach ($custom_ini AS $k => $v) {
            if (is_array($v)){
                if(isset($custom_ini[$k]) and !isset($config_ini[$k])) {
                    $config_ini[$k] =  $custom_ini[$k];
                } else {
                    $config_ini[$k] = configMerge($config_ini[$k], $custom_ini[$k]);
                }
            } else {
                $config_ini[$k] = $v;
            }
        }
        return $config_ini;
    }
}

# given a dataURI, returns array of UDI's that match.  (for GRIIDC, there should only be one)
# This is used to resolve a DOI request by given dataUri to an UDI via the dataUri in the
# registered metadata.
if(!function_exists('dataUri2Udi')) {
    # location to correctly use:
    function datasetUri2Udi($uri)
    {
        require_once 'db-utils.lib.php';
        $uri = '{'.$uri.'}';
        $sql =  "SELECT
                    dataset_udi
                 FROM
                    metadata
                    LEFT JOIN registry_view
                        ON
                            metadata.registry_id = registry_view.registry_id
                 WHERE
                    xpath('/gmi:MI_Metadata/gmd:distributionInfo[1]/gmd:MD_Distribution[1]/gmd:distributor[1]/gmd:MD_Distributor[1]/gmd:distributorTransferOptions[1]/gmd:MD_DigitalTransferOptions[1]/gmd:onLine[1]/gmd:CI_OnlineResource[1]/gmd:linkage[1]/gmd:URL[1]/text()', metadata_xml,
                    ARRAY[ARRAY['xmlns', 'http://www.isotc211.org/2005/gmi'],
                    ARRAY['gco', 'http://www.isotc211.org/2005/gco'],
                    ARRAY['gmd', 'http://www.isotc211.org/2005/gmd'],
                    ARRAY['gmi', 'http://www.isotc211.org/2005/gmi'],
                    ARRAY['gml', 'http://www.opengis.net/gml/3.2'],
                    ARRAY['gmx', 'http://www.isotc211.org/2005/gmx'],
                    ARRAY['gsr', 'http://www.isotc211.org/2005/gsr'],
                    ARRAY['gss', 'http://www.isotc211.org/2005/gss'],
                    ARRAY['gts', 'http://www.isotc211.org/2005/gts'],
                    ARRAY['xlink', 'http://www.w3.org/1999/xlink'],
                    ARRAY['xsi', 'http://www.w3.org/2001/XMLSchema-instance']
                    ])::text = :uri::text";

        $dbms = OpenDB('GOMRI_RO');
        $data = $dbms->prepare($sql);
        $ok = $data->bindParam(':uri',$uri, PDO::PARAM_STR);
        $data->execute();
        $rows = array();
        while ( $row = $data->fetch()) {
            array_push($rows,$row[0]);
        }
        $data = null;
        $dbms = null;
        unset($dbms);
        return $rows;
    }
}
