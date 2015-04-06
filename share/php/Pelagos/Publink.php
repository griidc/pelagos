<?php

namespace Pelagos;

class Publink
{
    private $udi;
    private $doi;

    public function createLink($udi,$doi)
    {
        $this->udi = $udi;
        $this->doi = $doi;
        $persistance = new Storage;
        $result = $persistance->store('Publink',$this);
    }

}
