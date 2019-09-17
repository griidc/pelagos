<?php

namespace Pelagos\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Fileset Entity class.
 *
 * @ORM\Entity
 */
class Fileset extends Entity
{
    /**
     * Collection of files.
     *
     * @var Collection
     *
     * @access protected
     *
     * @ORM\OneToMany(targetEntity="File", mappedBy="Fileset", cascade={"persist"}, orphanRemoval=true)
     */
    protected $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }


    /**
     * Getter for files.
     *
     * @return Collection
     */
    public function getFiles() : ? Collection
    {
        return $this->files;
    }

    /**
     * Setter for files.
     *
     * @param Collection $files The collection of files in this fileset.
     *
     * @return void
     */
    public function setFiles(Collection $files) : void
    {
        $this->files = $files;
    }
}
