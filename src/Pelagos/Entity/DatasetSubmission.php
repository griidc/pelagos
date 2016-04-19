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
     * A value for $restrictions that indicates the dataset has no restriction.
     */
    const RESTRICTION_NONE = 'None';

    /**
     * A value for $restrictions that indicates the dataset requires author's approval to be downloaded.
     */
    const RESTRICTION_APPROVAL = 'Approval';

    /**
     * A value for $restrictions that indicates the dataset may only be downloaded by the submitter.
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
     * A value for $datasetFileTransferType and $metadataFileTransferType that indicates transfer via direct upload.
     */
    const TRANSFER_TYPE_UPLOAD = 'upload';

    /**
     * A value for $datasetFileTransferType and $metadataFileTransferType that indicates transfer via SFTP or GridFTP.
     */
    const TRANSFER_TYPE_SFTP = 'SFTP';

    /**
     * A value for $datasetFileTransferType and $metadataFileTransferType that indicates transfer via HTTP or FTP pull.
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
     * A value for $datasetFileTransferStatus and $metadataFileTransferStatus that indicates the transfer has not yet been attempted.
     */
    const TRANSFER_STATUS_NONE = 'None';

    /**
     * A value for $datasetFileTransferStatus and $metadataFileTransferStatus that indicates the transfer has been completed.
     */
    const TRANSFER_STATUS_COMPLETED = 'Completed';

    /**
     * A value for $datasetFileTransferStatus and $metadataFileTransferStatus that indicates there was an error during transfer.
     */
    const TRANSFER_STATUS_ERROR = 'Error';

    /**
     * A value for $datasetFileTransferStatus and $metadataFileTransferStatus that indicates the URL needs review.
     */
    const TRANSFER_STATUS_NEEDS_REVIEW = 'NeedsReview';

    /**
     * A value for $datasetFileTransferStatus and $metadataFileTransferStatus that indicates that the dataset is remotely hosted.
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
     * @ORM\OneToOne(targetEntity="Dataset")
     */
    protected $dataset;

    /**
     * The ID for this Dataset Submission.
     *
     * Legacy DB column: registry_id
     *
     * @var string
     *
     * @ORM\Column
     */
    protected $datasetSubmissionId;

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
     * DOI for this dataset.
     *
     * Legacy DB column: doi
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $doi;

    /**
     * Dataset file transfer type.
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
     * Dataset file URL.
     *
     * Legacy DB column: url_data
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $datasetFileUrl;

    /**
     * Dataset file transfer status.
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
     * Dataset file name.
     *
     * Legacy DB column: dataset_filename
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $datasetFileName;

    /**
     * Dataset file size.
     *
     * Legacy DB column: dataset_download_size
     *
     * @var integer
     *
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $datasetFileSize;

    /**
     * Dataset file md5 hash.
     *
     * Legacy DB column: fs_md5_hash
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $datasetFileMd5Hash;

    /**
     * Dataset file sha1 hash.
     *
     * Legacy DB column: fs_sha1_hash
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $datasetFileSha1Hash;

    /**
     * Dataset file sha256 hash.
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
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $datasetFilePullDays;

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
     * Metadata file transfer type for this dataset.
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
     * Metadata file URL for this dataset.
     *
     * Legacy DB column: url_metadata
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $metadataFileUrl;

    /**
     * Metadata file transfer status.
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
     * Metadata file name for this dataset.
     *
     * Legacy DB column: dataset_metadata
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $metadataFileName;

    /**
     * Metadata file sha256 hash for this dataset.
     *
     * Legacy DB column: metadata_file_hash
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $metadataFileSha256Hash;

    /**
     * Status of the metadata for this dataset.
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
}
