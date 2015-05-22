<?php

namespace MetadataGenerator;

class XMLMetadataFile
{
    private function GetLocationUgly($udi)
    {
        # load global pelagos config
        $GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

        # load Common library from global share
        require_once($GLOBALS['config']['paths']['share'].'/php/Common.php');

        # check for local config file
        if (file_exists('config.ini')) {
            # merge local config with global config
            $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file('config.ini', true));
        }

        $metadataPath = $GLOBALS['config']['paths']['data_download'];
        $filePath = $metadataPath . '/' . $udi . '/' . $udi . '.met';

        return $filePath;
    }

    private function GetLocationFromDB($udi)
    {
    }

    public function GetXML($udi)
    {
        $xmlText = false;
        $path = $this->GetLocationUgly($udi);
        if (is_readable($path) ) {
            $xmlText = file_get_contents($path);
        }
        return $xmlText;
    }

}
