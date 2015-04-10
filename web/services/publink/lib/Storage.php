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

                $dbms = openDB("GOMRI_RW");

                $sql = "SELECT
                            count(*)
                        FROM
                            dataset2publication_link
                        WHERE
                            dataset_uid = :dataset_udi
                        AND
                            publication_doi = :publication_doi";

                $sth = $dbms->prepare($sql);

                $sth->bindParam(':dataset_udi',$udi);
                $sth->bindParam(':publication_doi',$doi);

                try {
                    $sth->execute();
                    $result = $sth->fetchAll(PDO::FETCH_ASSOC);
                    if($result[0] > 0) {
                        throw new Exception("Record already exists");
                    }
                } catch (\PDOException $exception) {
                    throw $exception;
                }

                $sql2 = "INSERT INTO dataset2publication_link (dataset_uid, publication_number,
                        person_number) values (:dataset_udi, :publication_doi, :person_number)";

                $sth2 = $dbms->prepare($sql2);
                $sth2->bindParam(':dataset_udi',$udi);
                $sth2->bindParam(':publication_doi',$doi);
                $sth2->bindParam(':person_number',$emp);

                try {
                    $sth2->execute();
                } catch (\PDOException $exception) {
                    throw $exception;
                }

            break;
        }
    }
}
