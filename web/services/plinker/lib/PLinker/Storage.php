<?php

namespace PLinker;

class Storage
{
    public function store($type, $obj)
    {
        include "DBUtils.php";
        switch ($type) {
            case "Publink":
                $doi = $obj->getDoi();
                $udi = $obj->getUdi();
                $emp = $obj->getLinkCreator();

                $dbms = openDB("GOMRI_RW", true);

                $sql0 = "SELECT
                                count(*)
                            FROM
                                publication
                            WHERE
                                publication_doi = :publication_doi";

                $sth0 = $dbms->prepare($sql0);
                $sth0->bindParam(':publication_doi', $doi);

                try {
                    $sth0->execute();
                    $result = $sth0->fetchAll(\PDO::FETCH_ASSOC);
                    if ($result[0]['count'] < 1) {
                        throw new \Exception("Record Does not exist in publication table");
                    }
                } catch (\PDOException $exception) {
                    throw $exception;
                }

                $sql = "SELECT
                                count(*)
                            FROM
                                dataset2publication_link
                            WHERE
                                dataset_udi = :dataset_udi
                            AND
                                publication_doi = :publication_doi";

                $sth = $dbms->prepare($sql);

                $sth->bindParam(':dataset_udi', $udi);
                $sth->bindParam(':publication_doi', $doi);

                try {
                    $sth->execute();
                    $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
                    if ($result[0]['count'] > 0) {
                        throw new \Exception("A link has already been established "
                        . "between the given dataset and publication.");
                    }
                } catch (\PDOException $exception) {
                    throw $exception;
                }

                $sql2 = "INSERT INTO dataset2publication_link (dataset_udi, publication_doi,
                            username) values (:dataset_udi, :publication_doi, :username)";

                $sth2 = $dbms->prepare($sql2);
                $sth2->bindParam(':dataset_udi', $udi);
                $sth2->bindParam(':publication_doi', $doi);
                $sth2->bindParam(':username', $emp);

                try {
                    $sth2->execute();
                } catch (\PDOException $exception) {
                    throw $exception;
                }

                break;
        }
    }

    public function remove($type, $obj)
    {
        include "DBUtils.php";
        switch ($type) {
            case "Publink":
                $doi = $obj->getDoi();
                $udi = $obj->getUdi();

                $dbms = openDB("GOMRI_RW", true);

                $sql = "SELECT
                                count(*)
                            FROM
                                dataset2publication_link
                            WHERE
                                dataset_udi = :dataset_udi
                            AND
                                publication_doi = :publication_doi";

                $sth = $dbms->prepare($sql);

                $sth->bindParam(':dataset_udi', $udi);
                $sth->bindParam(':publication_doi', $doi);

                try {
                    $sth->execute();
                    $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
                    if ($result[0]['count'] == 0) {
                        throw new \Exception("A link between the given doi and UDI does not exist.");
                    }
                } catch (\PDOException $exception) {
                    throw $exception;
                }

                $sql2 = "DELETE FROM dataset2publication_link
                             WHERE dataset_udi = :dataset_udi
                             AND publication_doi = :publication_doi;";

                $sth2 = $dbms->prepare($sql2);
                $sth2->bindParam(':dataset_udi', $udi);
                $sth2->bindParam(':publication_doi', $doi);

                try {
                    $sth2->execute();
                } catch (\PDOException $exception) {
                    throw $exception;
                }

                break;
        }
    }
}
