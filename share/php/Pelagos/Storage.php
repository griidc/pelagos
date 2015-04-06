<?php

namespace Pelagos;

class Storage
{
    public function store($type,$obj)
    {
        switch ($type) {
            case "Publink":
                $link = $obj;
                $doi = $link->doi;
                $udi = $link->udi;
                echo "pretending to store a publink linking $udi to $doi\n";
            break;
        }
    } 
}
