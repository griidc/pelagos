<?php

namespace DataLand;

class PubLink
{
    public function getLinksArray($udi)
    {
        require_once('DBUtils.php');

        $citations = array();

        $sql = "select publication_doi from dataset2publication_link
                where dataset_udi = :udi";

        $dbh = openDB("GOMRI_RW", true);
        $sth = $dbh->prepare($sql);
        $sth->bindParam(":udi", $udi);
        try {
            $sth->execute();
        } catch (\PDOException $e) {
            return null;
        }

        while ($dataRow = $sth->fetch(\PDO::FETCH_ASSOC)) {
            $pub = new \Pelagos\Entity\Publication($dataRow['publication_doi']);
            array_push($citations, $pub->getCitation()->asArray());
        }
        $sth = null;
        unset($sth);
        $dbh = null;
        return $citations;
    }
}
