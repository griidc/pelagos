<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

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
     * Getter for all files.
     *
     * @return Collection
     */
    public function getAllFiles() : Collection
    {
        return $this->files;
    }

    /**
     * Getter for processed files.
     *
     * @return Collection
     */
    public function getProcessedFiles() : Collection
    {
        return $this->files->filter(function (File $file) {
            return $file->getStatus() === File::FILE_DONE;
        });
    }

    /**
     * Getter for unprocessed/new files.
     *
     * @return Collection
     */
    public function getNewFiles() : Collection
    {
        return $this->files->filter(function (File $file) {
            return $file->getStatus() === File::FILE_NEW;
        });
    }

    /**
     * Getter for deleted files.
     *
     * @return Collection
     */
    public function getDeletedFiles() : Collection
    {
        return $this->files->filter(function (File $file) {
            return $file->getStatus() === File::FILE_DELETED;
        });
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
}
