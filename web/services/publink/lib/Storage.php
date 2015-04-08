<?php

namespace Pelagos;

class Storage
{
    public function store($type,$obj)
    {
        switch ($type) {
            case "Publink":
                $doi = $obj->get_doi();
                $udi = $obj->get_udi();
                $emp = $obj->get_linkCreator();
                $sql = "INSERT INTO dataset2publication_link (dataset_uid, publication_number,
                        person_number) values (:dataset_udi, :publication_doi, :person_number)";

                echo "pretending to store a publink linking dataset ($udi) to publication ($doi) by employeeNumber ($emp)\n";
            break;
        }
    }
}
