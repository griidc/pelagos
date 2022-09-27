<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * File Entity class.
 *
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 */
class File extends Entity
{
    /**
     * Status value for a File that has been newly uploaded, not yet proccessed by "Filer".
     */
    const FILE_NEW = 'new';

    /**
     * Status value for a File that has been deleted. (File still exists on disk!)
     */
    const FILE_DELETED = 'deleted';

    /**
     * Status value for a File that has been proccessed.
     */
    const FILE_DONE = 'done';

    /**
     * Status value for a File that has an error of any kind.
     */
    const FILE_ERROR = 'error';

    /**
     * Status value for a File that is added to process queue.
     */
    const FILE_IN_QUEUE = 'queued';

    /**
     * Fileset which contains this file.
     *
     * @var Fileset
     *
     * @ORM\ManyToOne(targetEntity="Fileset", inversedBy="files")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $fileset;

    /**
     * Filepath name.
     *
     * @var string
     *
     * @Serializer\Groups({"card"})
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $filePathName;

    /**
     * File size(bytes).
     *
     * @var integer
     *
     * @Serializer\Groups({"card"})
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
     * File location on disk.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $physicalFilePath;

    /**
     * The status of this File.
     *
     * @var string
     *
     * @see FILE_* constants.
     *
     * @Serializer\Groups({"card"})
     *
     * @ORM\Column(type="text")
     */
    protected $status = self::FILE_NEW;

    /**
     * Getter for Fileset.
     *
     * @return Fileset|null
     */
    public function getFileset(): ?Fileset
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
     * Getter for Filepath Name.
     *
     * @return string|null
     */
    public function getFilePathName(): ?string
    {
        return $this->filePathName;
    }

    /**
     * Setter for Filepath Name.
     *
     * @param string|null $filePathName Name of the file.
     *
     * @return void
     */
    public function setFilePathName(?string $filePathName): void
    {
        $this->filePathName = $filePathName;
    }

    /**
     * Getter for Filepath Extension.
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("fileExtension")
     * @Serializer\Groups({"card"})
     *
     * @return string|null
     */
    public function getFileExtension(): ?string
    {
        $filePathName = $this->getFilePathName();
        if (!empty($filePathName)) {
            $pathParts = pathinfo($filePathName);
            if (key_exists('extension', $pathParts)) {
                return $pathParts['extension'];
            }
        }
        return null;
    }

    /**
     * Getter for File Size.
     *
     * @return integer|null
     */
    public function getFileSize(): ?int
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
    public function getFileSha256Hash(): ?string
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
    public function getUploadedAt(): ?\DateTime
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
    public function getUploadedBy(): ?Person
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
    public function getDescription(): ?string
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
     * Getter for Physical File path.
     *
     * @return string|null
     */
    public function getPhysicalFilePath(): ?string
    {
        return $this->physicalFilePath;
    }

    /**
     * Setter for Physical File path.
     *
     * @param string|null $physicalFilePath Path/Location of the file.
     *
     * @return void
     */
    public function setPhysicalFilePath(?string $physicalFilePath): void
    {
        $this->physicalFilePath = $physicalFilePath;
    }

    /**
     * Getter for Status.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Setter for Status.
     *
     * @param string $status The status of the file.
     *
     * @see FILE_* constants.
     *
     * @return void
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * Get file path parts in an array.
     *
     * @param string $path The filepath which needs to exploded into parts.
     *
     * @return array
     */
    public function getFilePathParts(string $path = ''): array
    {
        $firstOccurrence = 1;
        $filepath = $this->getFilePathName();
        if ($path) {
            $filepath = str_replace($path . '/', '', $filepath, $firstOccurrence);
        }
        return explode('/', $filepath);
    }
}
