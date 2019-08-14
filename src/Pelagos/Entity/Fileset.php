<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Fileset Entity class.
 *
 * @ORM\Entity
 */
class Fileset extends Entity
{
    /**
     * Total file size in the fileset.
     *
     * @var integer
     *
     * @ORM\Column(name="totalFileSize", type="integer")
     */
    private $totalFileSize;

    /**
     * Number of files in the fileset.
     *
     * @var integer
     *
     * @ORM\Column(name="numberOfFiles", type="integer")
     */
    private $numberOfFiles;

    /**
     * Set totalFileSize.
     *
     * @param integer $totalFileSize Total file size in bytes.
     *
     * @return void
     */
    public function setTotalFileSize(int $totalFileSize)
    {
        $this->totalFileSize = $totalFileSize;
    }

    /**
     * Get totalFileSize.
     *
     * @return integer
     */
    public function getTotalFileSize() : int
    {
        return $this->totalFileSize;
    }

    /**
     * Set numberOfFiles.
     *
     * @param integer $numberOfFiles The number of files in this fileset.
     *
     * @return void
     */
    public function setNumberOfFiles(int $numberOfFiles)
    {
        $this->numberOfFiles = $numberOfFiles;
    }

    /**
     * Get numberOfFiles.
     *
     * @return integer
     */
    public function getNumberOfFiles() : int
    {
        return $this->numberOfFiles;
    }
}
