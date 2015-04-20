<?php

namespace Plinker;

class Publink
{
    private $udi;
    private $doi;
    private $linkCreator;

    public function get_doi()
    {
        return $this->doi;
    }

    public function get_udi()
    {
        return $this->udi;
    }

    public function get_linkCreator()
    {
        return $this->linkCreator;
    }

    public function createLink($udi,$doi,$linkCreator)
    {
        $this->udi = $udi;
        $this->doi = $doi;
        $this->linkCreator = $linkCreator;
        $persistance = new Storage;
        $result = $persistance->store('Publink',$this);
    }

}
