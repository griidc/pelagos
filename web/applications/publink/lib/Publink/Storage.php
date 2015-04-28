<?php
namespace Publink;

class Storage
{
    public function getAll($type)
    {
        include "DBUtils.php";
        switch ($type) {
            case "Publink":
                $sql = "SELECT
                            dataset_udi,
                            publication_doi,
                            username,
                            TO_CHAR(
                                      TO_TIMESTAMP(
                                                    dataset2publication_createtime,
                                                    'YYYY-MM-DD HH24:MI:SS.US'
                                                  ),
                                      'YYYY-MM-DD HH24:MI'
                            ) AS createtime
                            FROM
                                dataset2publication_link
                            ORDER BY
                                dataset2publication_createtime desc";
                $dbh = openDB("GOMRI_RO", true);
                $sth = $dbh->prepare($sql);

                try {
                    $sth->execute();
                } catch (\PDOException $exception) {
                    throw $exception;
                }
                $inside = array();
                while ($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
                    $inside[] = array(
                                      'udi'       => $row['dataset_udi'],
                                      'doi'       => $row['publication_doi'],
                                      'username'  => $row['username'],
                                      'created'   => $row['createtime']
                                      );
                }
                $sth = null;
                $dbh = null;
                return $inside;
            break;
        }
    }
}
