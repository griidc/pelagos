<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * Fileset Entity class.
 *
 * @ORM\Entity(repositoryClass="App\Repository\FilesetRepository")
 */
class Fileset
{

    /**
     * Doctrine identifier for Fileset entity.
     *
     * @var integer
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * Collection of files.
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="File", mappedBy="Fileset", cascade={"persist"}, orphanRemoval=true)
     */
    protected $files;

    /**
     * Getter for identifier for Fileset entity.
     *
     * @return integer|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * Remover for Distribution Point.
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
