<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;

/**
 * Fileset Entity class.
 *
 * @ORM\Entity(repositoryClass="App\Repository\FilesetRepository")
 */
class Fileset extends Entity
{
    /**
     * Collection of files.
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="File", mappedBy="fileset", cascade={"persist"}, orphanRemoval=true)
     */
    protected $files;

    /**
     * Fileset constructor.
     */
    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    /**
     * Getter for files.
     *
     * @return Collection
     */
    public function getFiles() : Collection
    {
        return $this->files;
    }

    /**
     * Adder for file entity.
     *
     * @param File $file Single file to be added.
     *
     * @return void
     */
    public function addFile(File $file)
    {
        $file->setFileset($this);
        $this->files->add($file);
    }

    /**
     * Remover for File entity.
     *
     * @param File $file Single file to be removed.
     *
     * @return void
     */
    public function removeFile(File $file)
    {
        $this->files->removeElement($file);
    }

    /**
     * Check if the fileset is done processesing.
     *
     * @return boolean
     */
    public function isDone() :bool
    {
        $criteria = Criteria::create()
        ->where(
            new Comparison(
                'status',
                Comparison::IN,
                array(
                    File::FILE_NEW,
                    File::FILE_IN_PROGRESS
                )
            )
        );

        return count($this->files->matching($criteria)) === 0;
    }
}
