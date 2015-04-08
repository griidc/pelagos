<?php

namespace Pelagos;

class Publink
{
    private $udi;
    private $doi;

    public function get_doi()
    {
        return $this->doi;
    }

    public function get_udi()
    {
        return $this->udi;
    }

    public function createLink($udi,$doi)
    {
        $this->udi = $udi;
        $this->doi = $doi;
        $persistance = new Storage;
        $result = $persistance->store('Publink',$this);
    }

}
