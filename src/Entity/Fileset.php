<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;

/**
 * Fileset Entity class.
 */
#[ORM\Entity(repositoryClass: 'App\Repository\FilesetRepository')]
class Fileset
{
    use EntityTrait;
    use EntityIdTrait;
    use EntityDateTimeTrait;

    /**
     * Collection of files.
     *
     * @var Collection
     */
    #[ORM\OneToMany(targetEntity: 'File', mappedBy: 'fileset', cascade: ['persist'], orphanRemoval: true)]
    protected $files;

    /**
     * Zipped files path on disk.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $zipFilePath;

    /**
     * Zipped files size on disk.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $zipFileSize;

    /**
     * Zipped files hash on disk.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $zipFileSha256Hash;

    #[ORM\OneToOne(targetEntity: 'DatasetSubmission', mappedBy: 'fileset', cascade: ['persist', 'remove'])]
    protected $datasetSubmission;


    /**
     * Getter for Dataset Submission.
     *
     * @return DatasetSubmission|null
     */
    public function getDatasetSubmission(): ?DatasetSubmission
    {
        return $this->datasetSubmission;
    }

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
    public function getAllFiles(): Collection
    {
        return $this->files;
    }

    /**
     * Getter for processed files.
     *
     * @return Collection
     */
    public function getProcessedFiles(): Collection
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
    public function getNewFiles(): Collection
    {
        return $this->files->filter(function (File $file) {
            return $file->getStatus() === File::FILE_NEW;
        });
    }

    /**
     * Getter for queued files.
     *
     * @return Collection
     */
    public function getQueuedFiles(): Collection
    {
        return $this->files->filter(function (File $file) {
            return $file->getStatus() === File::FILE_IN_QUEUE;
        });
    }

    /**
     * Getter for deleted files.
     *
     * @return Collection
     */
    public function getDeletedFiles(): Collection
    {
        return $this->files->filter(function (File $file) {
            return $file->getStatus() === File::FILE_DELETED;
        });
    }

     /**
     * Getter for file by ID.
     *
     * @param integer $id The file ID.
     *
     * @throws Exception When the file by ID was not found.
     *
     * @return File|null
     */
    public function getFileById(int $id): ?File
    {
        $files = $this->files->filter(function (File $file) use ($id) {
            return $file->getId() === $id;
        });

        if ($files->count() === 0) {
            throw new \Exception("File with id:$id not found!");
        }

        return $files->first();
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
    public function isDone(): bool
    {
        $criteria = Criteria::create()
        ->where(
            new Comparison(
                'status',
                Comparison::IN,
                array(
                    File::FILE_NEW,
                    File::FILE_IN_QUEUE
                )
            )
        );

        return count($this->files->matching($criteria)) === 0;
    }

    /**
     * Check if any file is in queue.
     *
     * @return boolean
     */
    public function isQueued(): bool
    {
        $criteria = Criteria::create()
            ->where(
                new Comparison(
                    'status',
                    Comparison::IN,
                    array(
                        File::FILE_IN_QUEUE
                    )
                )
            );

        return count($this->files->matching($criteria)) > 0;
    }


    /**
     * Checks if file exists.
     *
     * @param string $newFileName Filename of the new file to check.
     *
     * @return bool
     */
    public function doesFileExist(string $newFileName): bool
    {
        $processedAndNewFiles = $this->getProcessedAndNewFiles();
        return $processedAndNewFiles->exists(function ($key, File $file) use ($newFileName) {
            return $file->getFilePathName() === $newFileName;
        });
    }

    /**
     * Returns the total filesize for this fileset.
     *
     * @return integer
     */
    public function getFileSize(): int
    {
        $fileSize = 0;

        foreach ($this->getProcessedAndNewFiles() as $file) {
            $fileSize += (int) $file->getFileSize();
        }

        return $fileSize;
    }

    /**
     * Gets the existing File entity.
     *
     * @param string $fileName Filename key to get the File entity.
     *
     * @return File
     */
    public function getExistingFile(string $fileName): File
    {
        $criteria = Criteria::create()
            ->where(
                new Comparison(
                    'filePathName',
                    Comparison::EQ,
                    $fileName
                )
            );

        return $this->files->matching($criteria)->first();
    }

    /**
     * Get processed and new files.
     *
     * @return Collection
     */
    public function getProcessedAndNewFiles(): Collection
    {
        return $this->files->filter(function (File $file) {
            return in_array($file->getStatus(), [FILE::FILE_NEW, FILE::FILE_DONE, FILE::FILE_IN_QUEUE]);
        });
    }

    /**
     * Gets the files in the given directory.
     *
     * @param string $path Parent directory path.
     *
     * @return Collection
     */
    public function getFilesInDirectory(string $path): Collection
    {
        return $this->getProcessedAndNewFiles()->filter(function (File $file) use ($path) {
            if ($path === '') {
                return true;
            }
            $path = "$path/";
            $dirName = dirname($file->getFilePathName());
            $pathPosition = strpos($file->getFilePathName(), $path);
            if ($dirName === $path || $pathPosition === 0) {
                return true;
            }
            return false;
        });
    }

    /**
     * Getter for zip file path.
     *
     * @return string|null
     */
    public function getZipFilePath(): ?string
    {
        return $this->zipFilePath;
    }

    /**
     * Setter for zip file path.
     *
     * @param string $zipFilePath Filepath on disk.
     *
     * @return void
     */
    public function setZipFilePath(string $zipFilePath): void
    {
        $this->zipFilePath = $zipFilePath;
    }

    /**
     * Getter for zip file size.
     *
     * @return string|null
     */
    public function getZipFileSize(): ?string
    {
        return $this->zipFileSize;
    }

    /**
     * Setter for zip file size.
     *
     * @param string $zipFileSize Zip file size on disk.
     *
     * @return void
     */
    public function setZipFileSize(string $zipFileSize): void
    {
        $this->zipFileSize = $zipFileSize;
    }

    /**
     * Getter for zip file hash value.
     *
     * @return string|null
     */
    public function getZipFileSha256Hash(): ?string
    {
        return $this->zipFileSha256Hash;
    }

    /**
     * Setter for zip file hash.
     *
     * @param string $zipFileSha256Hash Hash value for the zip file.
     *
     * @return void
     */
    public function setZipFileSha256Hash(string $zipFileSha256Hash): void
    {
        $this->zipFileSha256Hash = $zipFileSha256Hash;
    }

    /**
     * Check if zip file exists for the fileset.
     *
     * @return bool
     */
    public function doesZipFileExist(): bool
    {
        $fileExists = false;
        if (
            $this->getZipFilePath() and $this->getZipFileSha256Hash() and
            $this->getZipFileSize()
        ) {
            $fileExists = true;
        }
        return $fileExists;
    }

    /**
     * Returns the root path where the file is physically located.
     *
     * @return string
     */
    public function getFileRootPath(): string
    {
        return str_replace(
            ':',
            '.',
            $this->getDatasetSubmission()->getDataset()->getUdi()
        ) . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the number of files in the fileset.
     *
     * @return integer
     */
    public function getNumberOfFiles(): int
    {
        return count($this->getProcessedAndNewFiles());
    }

    /**
     * Returns the number of errored files in the fileset.
     *
     * @return integer
     */
    public function getNumberOfErroredFiles(): int
    {
        return count($this->files->filter(function (File $file) {
            return $file->getStatus() === File::FILE_ERROR;
        }));
    }
}
