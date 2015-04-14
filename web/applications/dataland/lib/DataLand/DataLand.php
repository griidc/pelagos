<?php

namespace DataLand;

class PubLink
{
    public function getLinksArray($udi)
    {
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
