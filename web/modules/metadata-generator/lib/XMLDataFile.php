<?php

namespace MetadataGenerator;

class XMLDataFile
{
    private function getLocationUgly($udi)
    {
        # load global pelagos config
        $GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

        # load Common library from global share
        require_once(__DIR__.'/../../../../share/php/Common.php');

        # check for local config file
        if (file_exists(__DIR__.'/config.ini')) {
            # merge local config with global config
            $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file('config.ini', true));
        }

        $metadataPath = $GLOBALS['config']['paths']['data_download'];
        $filePath = $metadataPath . '/' . $udi . '/' . $udi . '.met';

        return $filePath;
    }

    private function getLocationFromDB($udi)
    {

        # load global pelagos config
        $GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

        # load Common libraries
        require_once(__DIR__.'/../../../../share/php/Common.php');
        require_once(__DIR__.'/../../../../share/php/DBUtils.php');

        # check for local config file
        if (file_exists(__DIR__.'/config.ini')) {
            # merge local config with global config
            $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file('config.ini', true));
        }

        $metadataPath = $GLOBALS['config']['paths']['data_download'];

        $sql = "SELECT REGEXP_REPLACE(
                                        REGEXP_REPLACE(
                                                        dataset_metadata,
                                                        '-metadata.xml$', ''),
                                        '-',
                                        ':')
               AS dataset_metadata
               FROM registry_view
               WHERE dataset_udi = :udi";

        $dbh = OpenDB("GOMRI_RO");
        $db_error = false;

        $sth = $dbh->prepare($sql);
        $sth->bindparam(":udi", $udi);

        try {
            $sth->execute();
        } catch (\PDOException $e) {
            $db_error = true;
        }

        $data = $sth->fetchAll();

        if (($db_error == false) and ($sth->rowCount() > 0)) {
            $filepath = $metadataPath . '/' . $data[0][0] . '/' . $data[0][0] .  '.met';
            $sth = null;
            $dbh = null;
            return $filepath;
        } else {
            $sth = null;
            $dbh = null;
            return false;
        }

    }

    public function getXML($udi)
    {
        $xmlText = false;
        $path = $this->getLocationFromDB($udi);
        if (is_readable($path)) {
            $xmlText = file_get_contents($path);
        }
        return $xmlText;
    }
}
