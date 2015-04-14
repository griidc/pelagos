<?php

namespace PLinker;

class Publink
{
    private $udi;
    private $doi;
    private $linkCreator;

    public function getDoi()
    {
        return $this->doi;
    }

    public function getUdi()
    {
        return $this->udi;
    }

    public function getLinkCreator()
    {
        return $this->linkCreator;
    }

    public function createLink($udi, $doi, $linkCreator)
    {
        $this->udi = $udi;
        $this->doi = $doi;
        $this->linkCreator = $linkCreator;
        $persistance = new Storage;
        $result = $persistance->store('Publink', $this);
    }

    public function removeLink($udi, $doi, $linkCreator)
    {
        $this->udi = $udi;
        $this->doi = $doi;
        $this->linkCreator = $linkCreator;
        $persistance = new Storage;
        $result = $persistance->remove('Publink', $this);
    }
}
