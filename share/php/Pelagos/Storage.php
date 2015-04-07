<?php

namespace Pelagos;

class Storage
{
    public function remove($type,$obj)
    {
        switch ($type) {
            case "Publink":
                $link = $obj;
                $doi = $link->get_doi();
                $udi = $link->get_udi();
                echo "pretending to remove a publink linking $udi to $doi\n";
            break;
        }
    }
}

