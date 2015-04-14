<?php

namespace DataLand;

class PubLink
{
    public function getLinksArray($udi)
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
        # add pelagos/share/php to the include path
        set_include_path(get_include_path() . PATH_SEPARATOR . $GLOBALS['config']['paths']['share'] . '/php');
        require_once('DBUtils.php');

        $citations = array();

        $sql = "select publication_doi from dataset2publication_link
                where dataset_udi = :udi";

        $dbh = openDB("GOMRI_RW");
        $sth = $dbh->prepare($sql);
        $sth->bindParam(":udi", $udi);
        try {
            $sth->execute();
        } catch (\PDOException $e) {
            return null;
        }

        while ($dataRow = $sth->fetch(\PDO::FETCH_ASSOC)) {
            $doi = $dataRow['publication_doi'];
            $pub = new \Pelagos\Publication($doi);
            $citation = $pub->getCitation();
            $text = json_decode($citation->asJSON(), true)['text'];
            array_push($citations, array('doi' => $doi, 'citation' => $text));
        }
        $sth = null;
        unset($sth);
        $dbh = null;
        return $citations;
    }
}
