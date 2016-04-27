<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Dataset Submission Entity class.
 *
 * Legacy DB table: registry
 *
 * Legacy DB columns excluded:
 *     username - not used
 *     password - not used
 *     dataset_udi - now in Dataset entity
 *     submittimestamp - now creationTimeStamp inherited from Entity
 *     userid - now creator inherited from Entity
 *     authentication - not used
 *     generatedoi - not used
 *     dataset_download_start_datetime - not used
 *     dataset_download_end_datetime - not used
 *     dataset_uuid - not used
 *     dataset_download_error_log - not used
 *     user_supplied_hash - not used
 *     hash_algorithm - not used
 *     approval_status - not used
 *     jira_ticket - probably belongs in Dataset entity?
 *
 * @ORM\Entity
 */
class DatasetSubmission extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Dataset Submission';

    /**
     * Indicates the dataset has no restriction.
     */
    const RESTRICTION_NONE = 'None';

    /**
     * Indicates the dataset requires author's approval to be downloaded.
     */
    const RESTRICTION_APPROVAL = 'Approval';

    /**
     * Indicates the dataset may only be downloaded by the submitter.
     */
    const RESTRICTION_RESTRICTED = 'Restricted';

    /**
     * Valid values for $restrictions.
     */
    const RESTRICTIONS = array(
        'None' => self::RESTRICTION_NONE,
        'Requires Author\'s Approval' => self::RESTRICTION_APPROVAL,
        'Restricted' => self::RESTRICTION_RESTRICTED,
    );

    /**
     * Indicates transfer via direct upload.
     */
    const TRANSFER_TYPE_UPLOAD = 'upload';

    /**
     * Indicates transfer via SFTP or GridFTP.
     */
    const TRANSFER_TYPE_SFTP = 'SFTP';

    /**
     * Indicates transfer via HTTP or FTP pull.
     */
    const TRANSFER_TYPE_HTTP = 'HTTP';

    /**
     * Valid values for $datasetFileTransferType and $metadataFileTransferType.
     */
    const TRANSFER_TYPES = array(
        'Direct Upload' => self::TRANSFER_TYPE_UPLOAD,
        'Upload via SFTP/GridFTP' => self::TRANSFER_TYPE_SFTP,
        'Request Pull from HTTP/FTP Server' => self::TRANSFER_TYPE_HTTP,
    );

    /**
     * Indicates the transfer has not yet been attempted.
     */
    const TRANSFER_STATUS_NONE = 'None';

    /**
     * Indicates the transfer has been completed.
     */
    const TRANSFER_STATUS_COMPLETED = 'Completed';

    /**
     * Indicates there was an error during transfer.
     */
    const TRANSFER_STATUS_ERROR = 'Error';

    /**
     * Indicates the URL needs review.
     */
    const TRANSFER_STATUS_NEEDS_REVIEW = 'NeedsReview';

    /**
     * Indicates that the dataset is remotely hosted.
     */
    const TRANSFER_STATUS_REMOTELY_HOSTED = 'RemotelyHosted';

    /**
     * Valid values for $datasetFileTransferStatus and $metadataFileTransferStatus.
     */
    const TRANSFER_STATUSES = array(
        'Not Yet Transferred' => self::TRANSFER_STATUS_NONE,
        'Transfer Complete' => self::TRANSFER_STATUS_COMPLETED,
        'Transfer Error' => self::TRANSFER_STATUS_ERROR,
        'URL Needs Review' => self::TRANSFER_STATUS_NEEDS_REVIEW,
        'Remotely Hosted' => self::TRANSFER_STATUS_REMOTELY_HOSTED,
    );

    /**
     * A value for $metadataStatus that indicates no status has been set.
     */
    const METADATA_STATUS_NONE = 'None';

    /**
     * A value for $metadataStatus that indicates that metadata has been submitted.
     */
    const METADATA_STATUS_SUBMITTED = 'Submitted';

    /**
     * A value for $metadataStatus that indicates that the metadata is in review.
     */
    const METADATA_STATUS_IN_REVIEW = 'InReview';

    /**
     * A value for $metadataStatus that indicates that the metadata is undergoing a second check.
     */
    const METADATA_STATUS_SECOND_CHECK = 'SecondCheck';

    /**
     * A value for $metadataStatus that indicates that the metadata has been accepted.
     */
    const METADATA_STATUS_ACCEPTED = 'Accepted';

    /**
     * A value for $metadataStatus that indicates that the metadata has been sent back to the submitter for revision.
     */
    const METADATA_STATUS_BACK_TO_SUBMITTER = 'BackToSubmitter';

    /**
     * Valid values for $metadataStatus.
     */
    const METADATA_STATUSES = array(
        'No Status' => self::METADATA_STATUS_NONE,
        'Submitted' => self::METADATA_STATUS_SUBMITTED,
        'In Review' => self::METADATA_STATUS_IN_REVIEW,
        '2nd Check' => self::METADATA_STATUS_SECOND_CHECK,
        'Accepted' => self::METADATA_STATUS_ACCEPTED,
        'Bk To Sub' => self::METADATA_STATUS_BACK_TO_SUBMITTER,
    );

    /**
     * The Dataset this Dataset Submission is attached to.
     *
     * @var Dataset
     *
     * @ORM\ManyToOne(targetEntity="Dataset", inversedBy="datasetSubmissionHistory")
     */
    protected $dataset;

    /**
     * The sequence for this Dataset Submission.
     *
     * This should be incremented for each submission for the same dataset.
     *
     * Legacy DB column: registry_id (the sequence portion)
     *
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $sequence;

    /**
     * The title for this Dataset Submission.
     *
     * Legacy DB column: dataset_title
     *
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(
     *     message="Title is required"
     * )
     */
    protected $title;

    /**
     * The abstract for this Dataset Submission.
     *
     * Legacy DB column: dataset_abstract
     *
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(
     *     message="Abstract is required"
     * )
     */
    protected $abstract;

    /**
     * The author(s) for this Dataset Submission.
     *
     * Legacy DB column: dataset_originator
     *
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(
     *     message="At least one author is required"
     * )
     */
    protected $authors;

    /**
     * The Point of Contact Name for this Dataset Submission.
     *
     * Legacy DB column: dataset_poc_name
     *
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(
     *     message="Point of Contact Name is required"
     * )
     */
    protected $pointOfContactName;

    /**
     * The Point of Contact E-Mail for this Dataset Submission.
     *
     * Legacy DB column: dataset_poc_email
     *
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(
     *     message="Point of Contact E-Mail is required"
     * )
     */
    protected $pointOfContactEmail;

    /**
     * Whether the dataset has any restrictions.
     *
     * Legacy DB column: access_status
     *
     * @var string
     *
     * @see RESTRICTIONS class constant for valid values.
     *
     * @ORM\Column(nullable=true)
     */
    protected $restrictions;

    /**
     * The DOI for this dataset.
     *
     * Legacy DB column: doi
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $doi;

    /**
     * The dataset file transfer type.
     *
     * Legacy DB column: data_server_type
     *
     * @var string
     *
     * @see TRANSFER_TYPES class constant for valid values.
     *
     * @ORM\Column(nullable=true)
     */
    protected $datasetFileTransferType;

    /**
     * The dataset file.
     *
     * This is used for direct upload.
     *
     * Legacy DB column: url_data
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $datasetFile;

    /**
     * The dataset file path.
     *
     * This is used for SFTP/GridFTP.
     *
     * Legacy DB column: url_data
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $datasetFilePath;

    /**
     * The dataset file URL.
     *
     * This is used for HTTP/FTP pull.
     *
     * Legacy DB column: url_data
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $datasetFileUrl;

    /**
     * The dataset file transfer status.
     *
     * Legacy DB column: dataset_download_status
     *
     * @var string
     *
     * @see TRANSFER_STATUSES class constant for valid values.
     *
     * @ORM\Column(nullable=true)
     */
    protected $datasetFileTransferStatus;

    /**
     * The dataset file name.
     *
     * Legacy DB column: dataset_filename
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $datasetFileName;

    /**
     * The dataset file size.
     *
     * Legacy DB column: dataset_download_size
     *
     * @var integer
     *
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $datasetFileSize;

    /**
     * The dataset file md5 hash.
     *
     * Legacy DB column: fs_md5_hash
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $datasetFileMd5Hash;

    /**
     * The dataset file sha1 hash.
     *
     * Legacy DB column: fs_sha1_hash
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $datasetFileSha1Hash;

    /**
     * The dataset file sha256 hash.
     *
     * Legacy DB column: fs_sha256_hash
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $datasetFileSha256Hash;

    /**
     * The date after which the dataset file will be available for pull.
     *
     * Legacy DB column: availability_date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $datasetFileAvailabilityDate;

    /**
     * Whether the dataset should only be pulled at certain times.
     *
     * Legacy DB column: access_period
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $datasetFilePullCertainTimesOnly;

    /**
     * The time of day to start pulling this dataset.
     *
     * Legacy DB column: access_period_start
     *
     * @var \DateTime
     *
     * @ORM\Column(type="time", nullable=true)
     */
    protected $datasetFilePullStartTime;

    /**
     * Days this dataset can be pulled.
     *
     * Legacy DB column: access_period_weekdays
     *
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $datasetFilePullDays = array();

    /**
     * Whether to pull the source data.
     *
     * Legacy DB column: data_source_pull
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $datasetFilePullSourceData;

    /**
     * The metadata file transfer type.
     *
     * Legacy DB column: metadata_server_type
     *
     * @var string
     *
     * @see TRANSFER_TYPES class constant for valid values.
     *
     * @ORM\Column(nullable=true)
     */
    protected $metadataFileTransferType;

    /**
     * The metadata file.
     *
     * This is used for direct upload.
     *
     * Legacy DB column: url_metadata
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $metadataFile;

    /**
     * The metadata file path.
     *
     * This is used for SFTP/GridFTP.
     *
     * Legacy DB column: url_metadata
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $metadataFilePath;

    /**
     * The metadata file URL.
     *
     * This is used for HTTP/FTP pull.
     *
     * Legacy DB column: url_metadata
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $metadataFileUrl;

    /**
     * The metadata file transfer status.
     *
     * Legacy DB column: metadata_dl_status
     *
     * @var string
     *
     * @see TRANSFER_STATUSES class constant for valid values.
     *
     * @ORM\Column(nullable=true)
     */
    protected $metadataFileTransferStatus;

    /**
     * The metadata file name.
     *
     * Legacy DB column: dataset_metadata
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $metadataFileName;

    /**
     * The metadata file sha256 hash.
     *
     * Legacy DB column: metadata_file_hash
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $metadataFileSha256Hash;

    /**
     * Status of the metadata.
     *
     * Legacy DB column: metadata_status
     *
     * @var string
     *
     * @see METADATA_STATUSES class constant for valid values.
     *
     * @ORM\Column(nullable=true)
     */
    protected $metadataStatus;

    /**
     * Set the Dataset this Dataset Submission is attached to.
     *
     * @param Dataset $dataset The Dataset this Dataset Submission is attached to.
     *
     * @return void
     */
    public function setDataset(Dataset $dataset)
    {
        $this->dataset = $dataset;
    }

    /**
     * Get the Dataset this Dataset Submission is attached to.
     *
     * @return Dataset
     */
    public function getDataset()
    {
        return $this->dataset;
    }

    /**
     * Set the sequence for this Dataset Submission.
     *
     * @param integer $sequence The sequence for this Dataset Submission.
     *
     * @throws \Exception When $sequence is not an integer.
     *
     * @return void
     */
    public function setSequence($sequence)
    {
        if ('integer' !== gettype($sequence)) {
            throw new \Exception('Seqeunce must be an integer');
        }
        $this->sequence = $sequence;
    }

    /**
     * Get the sequence for this Dataset Submission.
     *
     * @return string
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Set the title for this Dataset Submission.
     *
     * @param string $title The title for this Dataset Submission.
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get the title for this Dataset Submission.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the abstract for this Dataset Submission.
     *
     * @param string $abstract The abstract for this Dataset Submission.
     *
     * @return void
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * Get the abstract for this Dataset Submission.
     *
     * @return string
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * Set the author(s) for this Dataset Submission.
     *
     * @param string $authors The author(s) for this Dataset Submission.
     *
     * @return void
     */
    public function setAuthors($authors)
    {
        $this->authors = $authors;
    }

    /**
     * Get the author(s) for this Dataset Submission.
     *
     * @return string
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * Set the Point of Contact Name for this Dataset Submission.
     *
     * @param string $pointOfContactName The Point of Contact Name for this Dataset Submission.
     *
     * @return void
     */
    public function setPointOfContactName($pointOfContactName)
    {
        $this->pointOfContactName = $pointOfContactName;
    }

    /**
     * Get the Point of Contact Name for this Dataset Submission.
     *
     * @return string
     */
    public function getPointOfContactName()
    {
        return $this->pointOfContactName;
    }

    /**
     * Set the Point of Contact E-Mail for this Dataset Submission.
     *
     * @param string $pointOfContactEmail The Point of Contact E-Mail for this Dataset Submission.
     *
     * @return void
     */
    public function setPointOfContactEmail($pointOfContactEmail)
    {
        $this->pointOfContactEmail = $pointOfContactEmail;
    }

    /**
     * Get the Point of Contact E-Mail for this Dataset Submission.
     *
     * @return string
     */
    public function getPointOfContactEmail()
    {
        return $this->pointOfContactEmail;
    }

    /**
     * Set whether the dataset has any restrictions.
     *
     * @param string $restrictions Whether the dataset has any restrictions.
     *
     * @see RESTRICTIONS class constant for valid values.
     *
     * @return void
     */
    public function setRestrictions($restrictions)
    {
        $this->restrictions = $restrictions;
    }

    /**
     * Get whether the dataset has any restrictions.
     *
     * @return string
     */
    public function getRestrictions()
    {
        return $this->restrictions;
    }

    /**
     * Set the DOI for this dataset.
     *
     * @param string $doi The DOI for this dataset.
     *
     * @return void
     */
    public function setDoi($doi)
    {
        $this->doi = $doi;
    }

    /**
     * Get the DOI for this dataset.
     *
     * @return string
     */
    public function getDoi()
    {
        return $this->doi;
    }

    /**
     * Set the dataset file transfer type.
     *
     * @param string $datasetFileTransferType The dataset file transfer type.
     *
     * @see TRANSFER_TYPES class constant for valid values.
     *
     * @return void
     */
    public function setDatasetFileTransferType($datasetFileTransferType)
    {
        $this->datasetFileTransferType = $datasetFileTransferType;
    }

    /**
     * Get the dataset file transfer type.
     *
     * @return string
     */
    public function getDatasetFileTransferType()
    {
        return $this->datasetFileTransferType;
    }

    /**
     * Set the dataset file.
     *
     * This is used for direct upload.
     *
     * @param string $datasetFile The dataset file.
     *
     * @return void
     */
    public function setDatasetFile($datasetFile)
    {
        $this->datasetFile = $datasetFile;
    }

    /**
     * Get the dataset file.
     *
     * This is used for direct upload.
     *
     * @return string
     */
    public function getDatasetFile()
    {
        return $this->datasetFile;
    }

    /**
     * Set the dataset file path.
     *
     * This is used for SFTP/GridFTP.
     *
     * @param string $datasetFilePath The dataset file path.
     *
     * @return void
     */
    public function setDatasetFilePath($datasetFilePath)
    {
        $this->datasetFilePath = $datasetFilePath;
    }

    /**
     * Get the dataset file path.
     *
     * This is used for SFTP/GridFTP.
     *
     * @return string
     */
    public function getDatasetFilePath()
    {
        return $this->datasetFilePath;
    }

    /**
     * Set the dataset file URL.
     *
     * This is used for HTTP/FTP pull.
     *
     * @param string $datasetFileUrl The dataset file URL.
     *
     * @return void
     */
    public function setDatasetFileUrl($datasetFileUrl)
    {
        $this->datasetFileUrl = $datasetFileUrl;
    }

    /**
     * Get the dataset file URL.
     *
     * This is used for HTTP/FTP pull.
     *
     * @return string
     */
    public function getDatasetFileUrl()
    {
        return $this->datasetFileUrl;
    }

    /**
     * Set the dataset file transfer status.
     *
     * @param string $datasetFileTransferStatus The dataset file transfer status.
     *
     * @see TRANSFER_STATUSES class constant for valid values.
     *
     * @return void
     */
    public function setDatasetFileTransferStatus($datasetFileTransferStatus)
    {
        $this->datasetFileTransferStatus = $datasetFileTransferStatus;
    }

    /**
     * Get the dataset file transfer status.
     *
     * @return string
     */
    public function getDatasetFileTransferStatus()
    {
        return $this->datasetFileTransferStatus;
    }

    /**
     * Set the dataset file name.
     *
     * @param string $datasetFileName The dataset file name.
     *
     * @return void
     */
    public function setDatasetFileName($datasetFileName)
    {
        $this->datasetFileName = $datasetFileName;
    }

    /**
     * Get the dataset file name.
     *
     * @return string
     */
    public function getDatasetFileName()
    {
        return $this->datasetFileName;
    }

    /**
     * Set the dataset file size.
     *
     * @param integer $datasetFileSize The dataset file size.
     *
     * @return void
     */
    public function setDatasetFileSize($datasetFileSize)
    {
        $this->datasetFileSize = $datasetFileSize;
    }

    /**
     * Get the dataset file size.
     *
     * @return integer
     */
    public function getDatasetFileSize()
    {
        return $this->datasetFileSize;
    }

    /**
     * Set the dataset file md5 hash.
     *
     * @param string $datasetFileMd5Hash The dataset file md5 hash.
     *
     * @return void
     */
    public function setDatasetFileMd5Hash($datasetFileMd5Hash)
    {
        $this->datasetFileMd5Hash = $datasetFileMd5Hash;
    }

    /**
     * Set the dataset file md5 hash.
     *
     * @return string
     */
    public function getDatasetFileMd5Hash()
    {
        return $this->datasetFileMd5Hash;
    }

    /**
     * Set the dataset file sha1 hash.
     *
     * @param string $datasetFileSha1Hash The dataset file sha1 hash.
     *
     * @return void
     */
    public function setDatasetFileSha1Hash($datasetFileSha1Hash)
    {
        $this->datasetFileSha1Hash = $datasetFileSha1Hash;
    }

    /**
     * Get the dataset file sha1 hash.
     *
     * @return string
     */
    public function getDatasetFileSha1Hash()
    {
        return $this->datasetFileSha1Hash;
    }

    /**
     * Set the dataset file sha256 hash.
     *
     * @param string $datasetFileSha256Hash The dataset file sha256 hash.
     *
     * @return void
     */
    public function setDatasetFileSha256Hash($datasetFileSha256Hash)
    {
        $this->datasetFileSha256Hash = $datasetFileSha256Hash;
    }

    /**
     * Get the dataset file sha256 hash.
     *
     * @return string
     */
    public function getDatasetFileSha256Hash()
    {
        return $this->datasetFileSha256Hash;
    }

    /**
     * Set the date after which the dataset file will be available for pull.
     *
     * @param \DateTime|null $datasetFileAvailabilityDate The date after which the dataset
     *                                                    file will be available for pull.
     *
     * @return void
     */
    public function setDatasetFileAvailabilityDate(\DateTime $datasetFileAvailabilityDate = null)
    {
        $this->datasetFileAvailabilityDate = $datasetFileAvailabilityDate;
    }

    /**
     * Get the date after which the dataset file will be available for pull.
     *
     * @return \DateTime
     */
    public function getDatasetFileAvailabilityDate()
    {
        return $this->datasetFileAvailabilityDate;
    }

    /**
     * Set whether the dataset should only be pulled at certain times.
     *
     * @param boolean $datasetFilePullCertainTimesOnly Whether the dataset should only be pulled at certain times.
     *
     * @return void
     */
    public function setDatasetFilePullCertainTimesOnly($datasetFilePullCertainTimesOnly)
    {
        $this->datasetFilePullCertainTimesOnly = $datasetFilePullCertainTimesOnly;
    }

    /**
     * Get whether the dataset should only be pulled at certain times.
     *
     * @return boolean
     */
    public function getDatasetFilePullCertainTimesOnly()
    {
        return $this->datasetFilePullCertainTimesOnly;
    }

    /**
     * Set the time of day to start pulling this dataset.
     *
     * @param \DateTime|null $datasetFilePullStartTime The time of day to start pulling this dataset.
     *
     * @return void
     */
    public function setDatasetFilePullStartTime(\DateTime $datasetFilePullStartTime = null)
    {
        $this->datasetFilePullStartTime = $datasetFilePullStartTime;
    }

    /**
     * Set the time of day to start pulling this dataset.
     *
     * @return \DateTime
     */
    public function getDatasetFilePullStartTime()
    {
        return $this->datasetFilePullStartTime;
    }

    /**
     * Set the Days this dataset can be pulled.
     *
     * @param array $datasetFilePullDays The days this dataset can be pulled.
     *
     * @return void
     */
    public function setDatasetFilePullDays(array $datasetFilePullDays)
    {
        $this->datasetFilePullDays = $datasetFilePullDays;
    }

    /**
     * Get the Days this dataset can be pulled.
     *
     * @return string
     */
    public function getDatasetFilePullDays()
    {
        return $this->datasetFilePullDays;
    }

    /**
     * Set whether to pull the source data.
     *
     * @param boolean $datasetFilePullSourceData Whether to pull the source data.
     *
     * @return void
     */
    public function setDatasetFilePullSourceData($datasetFilePullSourceData)
    {
        $this->datasetFilePullSourceData = $datasetFilePullSourceData;
    }

    /**
     * Set whether to pull the source data.
     *
     * @return boolean
     */
    public function getDatasetFilePullSourceData()
    {
        return $this->datasetFilePullSourceData;
    }

    /**
     * Set the metadata file transfer type.
     *
     * @param string $metadataFileTransferType The metadata file transfer type.
     *
     * @see TRANSFER_TYPES class constant for valid values.
     *
     * @return void
     */
    public function setMetadataFileTransferType($metadataFileTransferType)
    {
        $this->metadataFileTransferType = $metadataFileTransferType;
    }

    /**
     * Get the metadata file transfer type.
     *
     * @return string
     */
    public function getMetadataFileTransferType()
    {
        return $this->metadataFileTransferType;
    }

    /**
     * Set the metadata file.
     *
     * This is used for direct upload.
     *
     * @param string $metadataFile The metadata file.
     *
     * @return void
     */
    public function setMetadataFile($metadataFile)
    {
        $this->metadataFile = $metadataFile;
    }

    /**
     * Get the metadata file URL.
     *
     * This is used for direct upload.
     *
     * @return string
     */
    public function getMetadataFile()
    {
        return $this->metadataFile;
    }

    /**
     * Set the metadata file path.
     *
     * This is used for SFTP/GridFTP.
     *
     * @param string $metadataFilePath The metadata file URL.
     *
     * @return void
     */
    public function setMetadataFilePath($metadataFilePath)
    {
        $this->metadataFilePath = $metadataFilePath;
    }

    /**
     * Get the metadata file path.
     *
     * This is used for SFTP/GridFTP.
     *
     * @return string
     */
    public function getMetadataFilePath()
    {
        return $this->metadataFilePath;
    }

    /**
     * Set the metadata file URL.
     *
     * This is used for HTTP/FTP pull.
     *
     * @param string $metadataFileUrl The metadata file URL.
     *
     * @return void
     */
    public function setMetadataFileUrl($metadataFileUrl)
    {
        $this->metadataFileUrl = $metadataFileUrl;
    }

    /**
     * Get the metadata file URL.
     *
     * This is used for HTTP/FTP pull.
     *
     * @return string
     */
    public function getMetadataFileUrl()
    {
        return $this->metadataFileUrl;
    }

    /**
     * Set the metadata file transfer status.
     *
     * @param string $metadataFileTransferStatus The metadata file transfer status.
     *
     * @see TRANSFER_STATUSES class constant for valid values.
     *
     * @return void
     */
    public function setMetadataFileTransferStatus($metadataFileTransferStatus)
    {
        $this->metadataFileTransferStatus = $metadataFileTransferStatus;
    }

    /**
     * Get the metadata file transfer status.
     *
     * @return string
     */
    public function getMetadataFileTransferStatus()
    {
        return $this->metadataFileTransferStatus;
    }

    /**
     * Set the metadata file name.
     *
     * @param string $metadataFileName The metadata file name.
     *
     * @return void
     */
    public function setMetadataFileName($metadataFileName)
    {
        $this->metadataFileName = $metadataFileName;
    }

    /**
     * Get the metadata file name.
     *
     * @return string
     */
    public function getMetadataFileName()
    {
        return $this->metadataFileName;
    }

    /**
     * Set the metadata file sha256 hash.
     *
     * @param string $metadataFileSha256Hash The metadata file sha256 hash.
     *
     * @return void
     */
    public function setMetadataFileSha256Hash($metadataFileSha256Hash)
    {
        $this->metadataFileSha256Hash = $metadataFileSha256Hash;
    }

    /**
     * Get the metadata file sha256 hash.
     *
     * @return string
     */
    public function getMetadataFileSha256Hash()
    {
        return $this->metadataFileSha256Hash;
    }

    /**
     * Set the status of the metadata.
     *
     * @param string $metadataStatus The status of the metadata.
     *
     * @see METADATA_STATUSES class constant for valid values.
     *
     * @return void
     */
    public function setMetadataStatus($metadataStatus)
    {
        $this->metadataStatus = $metadataStatus;
    }

    /**
     * Get the status of the metadata.
     *
     * @return string
     */
    public function getMetadataStatus()
    {
        return $this->metadataStatus;
    }

    /**
     * Get the Dataset Submission ID (UDI + 3 digit sequence).
     *
     * This is equivalent to the legacy registry_id.
     *
     * @return string
     */
    public function getDatasetSubmissionId()
    {
        return $this->dataset->getUdi() . '.' . strftime('%03d', $this->seqeunce);
    }
}
