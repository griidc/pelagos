<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * File Entity class.
 *
 * @ORM\Entity
 */
class File extends Entity
{
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
     * Fileset which contains this file.
     *
     * @var Fileset
     *
     * @ORM\ManyToOne(targetEntity="Fileset", inversedBy="files")
     * @ORM\JoinColumn(name="fileset_id", referencedColumnName="id", nullable=false)
     */
    protected $fileSet;

    /**
     * File constructor.
     *
     * @param Fileset $fileSet Fileset entity which this file belongs to.
     */
    public function __construct(Fileset $fileSet)
    {
        $this->fileSet = $fileSet;
    }

    /**
     * Getter for File Name.
     *
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Setter for File Name.
     *
     * @param string $fileName Name of the file.
     *
     * @return void
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * Getter for File Size.
     *
     * @return integer
     */
    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    /**
     * Setter for File Size.
     *
     * @param integer $fileSize Size of the file.
     *
     * @return void
     */
    public function setFileSize(int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    /**
     * Getter for File Hash.
     *
     * @return string
     */
    public function getFileSha256Hash(): string
    {
        return $this->fileSha256Hash;
    }

    /**
     * Setter for File Hash.
     *
     * @param string $fileSha256Hash Hash of the file.
     *
     * @return void
     */
    public function setFileSha256Hash(string $fileSha256Hash): void
    {
        $this->fileSha256Hash = $fileSha256Hash;
    }

    /**
     * Getter for File UploadedAt.
     *
     * @return \DateTime
     */
    public function getUploadedAt(): \DateTime
    {
        return $this->uploadedAt;
    }

    /**
     * Setter for File UploadedAt.
     *
     * @param \DateTime $uploadedAt File uploaded timestamp.
     *
     * @return void
     */
    public function setUploadedAt(\DateTime $uploadedAt): void
    {
        $this->uploadedAt = $uploadedAt;
    }

    /**
     * Getter for File UploadedBy.
     *
     * @return Person
     */
    public function getUploadedBy(): Person
    {
        return $this->uploadedBy;
    }

    /**
     * Setter for File UploadedBy.
     *
     * @param Person $uploadedBy Person who uploaded the file.
     *
     * @return void
     */
    public function setUploadedBy(Person $uploadedBy): void
    {
        $this->uploadedBy = $uploadedBy;
    }

    /**
     * Getter for File Description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Setter for File Description.
     *
     * @param string $description Description of the file.
     *
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Getter for File path.
     *
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Setter for File path.
     *
     * @param string $filePath Path/Location of the file.
     *
     * @return void
     */
    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }
}
