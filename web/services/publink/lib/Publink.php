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

    public function delink($udi,$doi)
    {
        $this->udi = $udi;
        $this->doi = $doi;
        $persistance = new Storage;
        $result = $persistance->remove('Publink',$this);
    }

}
