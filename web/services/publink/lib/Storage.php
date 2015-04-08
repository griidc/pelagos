<?php

namespace Pelagos;

class Storage
{
    public function store($type,$obj)
    {
        require("DBUtils.php");
        switch ($type) {
            case "Publink":
                $doi = $obj->get_doi();
                $udi = $obj->get_udi();
                $emp = $obj->get_linkCreator();
                $sql = "INSERT INTO dataset2publication_link (dataset_uid, publication_number,
                        person_number) values (:dataset_udi, :publication_doi, :person_number)";

                $dbms = openDB("GOMRI_RW");
                $sth = $dbms->prepare($sql);

                $sth->bindParam(':dataset_udi',$udi);
                $sth->bindParam(':publication_doi',$doi);
                $sth->bindParam(':person_number',$emp);

                try {
                    $sth->execute();
                } catch (\PDOException $exception) {
                    throw $exception;
                }
            break;
        }
    }
}
