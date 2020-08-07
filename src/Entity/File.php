<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * File Entity class.
 *
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 */
class File extends Entity
{
    /**
     * Fileset which contains this file.
     *
     * @var Fileset
     *
     * @ORM\ManyToOne(targetEntity="Fileset", inversedBy="files")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $fileset;

    /**
     * File name.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $fileName;

    /**
     * File size(bytes).
     *
     * @var integer
     *
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $fileSize;

    /**
     * File sha256 hash.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $fileSha256Hash;

    /**
     * The date the file is uploaded.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    protected $uploadedAt;

    /**
     * The Person who uploaded this file.
     *
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Person")
     */
    protected $uploadedBy;

    /**
     * Description of the file.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * File path or location.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $filePath;

    /**
     * Getter for Fileset.
     *
     * @return Fileset
     */
    public function getFileset(): Fileset
    {
        return $this->fileset;
    }

    /**
     * Getter for Fileset.
     *
     * @param Fileset $fileset An instance of Fileset entity.
     *
     * @return void
     */
    public function setFileset(Fileset $fileset): void
    {
        $this->fileset = $fileset;
    }

    /**
     * Getter for File Name.
     *
     * @return string|null
     */
    public function getFileName(): ? string
    {
        return $this->fileName;
    }

    /**
     * Setter for File Name.
     *
     * @param string|null $fileName Name of the file.
     *
     * @return void
     */
    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * Getter for File Size.
     *
     * @return integer|null
     */
    public function getFileSize(): ? int
    {
        return $this->fileSize;
    }

    /**
     * Setter for File Size.
     *
     * @param integer|null $fileSize Size of the file.
     *
     * @return void
     */
    public function setFileSize(?int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    /**
     * Getter for File Hash.
     *
     * @return string|null
     */
    public function getFileSha256Hash(): ? string
    {
        return $this->fileSha256Hash;
    }

    /**
     * Setter for File Hash.
     *
     * @param string|null $fileSha256Hash Hash of the file.
     *
     * @return void
     */
    public function setFileSha256Hash(?string $fileSha256Hash): void
    {
        $this->fileSha256Hash = $fileSha256Hash;
    }

    /**
     * Getter for File UploadedAt.
     *
     * @return \DateTime|null
     */
    public function getUploadedAt(): ? \DateTime
    {
        return $this->uploadedAt;
    }

    /**
     * Setter for File UploadedAt.
     *
     * @param \DateTime|null $uploadedAt File uploaded timestamp.
     *
     * @return void
     */
    public function setUploadedAt(?\DateTime $uploadedAt): void
    {
        $this->uploadedAt = $uploadedAt;
    }

    /**
     * Getter for File UploadedBy.
     *
     * @return Person
     */
    public function getUploadedBy(): ? Person
    {
        return $this->uploadedBy;
    }

    /**
     * Setter for File UploadedBy.
     *
     * @param Person|null $uploadedBy Person who uploaded the file.
     *
     * @return void
     */
    public function setUploadedBy(?Person $uploadedBy): void
    {
        $this->uploadedBy = $uploadedBy;
    }

    /**
     * Getter for File Description.
     *
     * @return string|null
     */
    public function getDescription(): ? string
    {
        return $this->description;
    }

    /**
     * Setter for File Description.
     *
     * @param string|null $description Description of the file.
     *
     * @return void
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * Getter for File path.
     *
     * @return string|null
     */
    public function getFilePath(): ? string
    {
        return $this->filePath;
    }

    /**
     * Setter for File path.
     *
     * @param string|null $filePath Path/Location of the file.
     *
     * @return void
     */
    public function setFilePath(?string $filePath): void
    {
        $this->filePath = $filePath;
    }
}
