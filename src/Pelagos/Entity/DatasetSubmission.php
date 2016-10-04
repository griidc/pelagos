<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

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
        self::RESTRICTION_NONE => 'None',
        self::RESTRICTION_APPROVAL => 'Requires Author\'s Approval',
        self::RESTRICTION_RESTRICTED => 'Restricted',
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
        self::TRANSFER_TYPE_UPLOAD => 'Direct Upload',
        self::TRANSFER_TYPE_SFTP => 'Upload via SFTP/GridFTP',
        self::TRANSFER_TYPE_HTTP => 'Request Pull from HTTP/FTP Server',
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
        self::TRANSFER_STATUS_NONE => 'Not Yet Transferred',
        self::TRANSFER_STATUS_COMPLETED => 'Transfer Complete',
        self::TRANSFER_STATUS_ERROR => 'Transfer Error',
        self::TRANSFER_STATUS_NEEDS_REVIEW => 'URL Needs Review',
        self::TRANSFER_STATUS_REMOTELY_HOSTED => 'Remotely Hosted',
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
        self::METADATA_STATUS_NONE => 'No Status',
        self::METADATA_STATUS_SUBMITTED => 'Submitted',
        self::METADATA_STATUS_IN_REVIEW => 'In Review',
        self::METADATA_STATUS_SECOND_CHECK => '2nd Check',
        self::METADATA_STATUS_ACCEPTED => 'Accepted',
        self::METADATA_STATUS_BACK_TO_SUBMITTER => 'Bk To Sub',
    );

    /**
     * No dataset submission has been submitted.
     */
    const STATUS_UNSUBMITTED = 0;

    /**
     * A dataset submission has been submitted, but no data file URI has been provided.
     */
    const STATUS_INCOMPLETE = 1;

    /**
     * A dataset submission has been submitted, and a data file URI has been provided.
     */
    const STATUS_COMPLETE = 2;

    /**
     * The dataset is not available to anyone.
     */
    const AVAILABILITY_STATUS_NOT_AVAILABLE = 0;

    /**
     * The dataset is not available because no metadata has been submitted.
     */
    const AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION = 2;

    /**
     * The dataset is not available because it does not yet have approved metadata.
     */
    const AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL = 4;

    /**
     * The dataset is marked as restricted to author use only, but is remotely hosted.
     */
    const AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED = 5;

    /**
     * The dataset is marked as available to users with approval, but is remotely hosted.
     */
    const AVAILABILITY_STATUS_AVAILABLE_WITH_APPROVAL_REMOTELY_HOSTED = 6;

    /**
     * The dataset is marked as publicly available, but is remotely hosted.
     */
    const AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED = 7;

    /**
     * The dataset is restricted to author use only.
     */
    const AVAILABILITY_STATUS_RESTRICTED = 8;

    /**
     * The dataset is available to users with approval.
     */
    const AVAILABILITY_STATUS_AVAILABLE_WITH_APPROVAL = 9;

    /**
     * The dataset is publicly available.
     */
    const AVAILABILITY_STATUS_PUBLICLY_AVAILABLE = 10;

    /**
     * Valid values for self::$referenceDateType.
     *
     * The array keys are the values to be set in self::referenceDateType.
     */
    const REFERENCE_DATE_TYPES = [
        'creation' => [
            'name' => 'Creation',
            'description' => 'The date that identifies when the resource was brought into existence.'
        ],
        'publication' => [
            'name' => 'Publication',
            'description' => 'The date that identifies when the resource was issued.'
        ],
        'revision' => [
            'name' => 'Revision',
            'description' => 'The date that identifies when the resource was improved or amended.'
        ],
    ];

    /**
     * Valid values for self::$temporalExtent.
     *
     * The array keys are the values to be set in self::temporalExtent.
     */
    const TEMPORAL_EXTENT_DESCRIPTIONS = [
        'groundcondition' => [
            'name' => 'Ground Condition',
            'description' => 'Data represent the actual condition of things on the ground during ' .
                             'the time period specified and may also be used to characterize data ' .
                             'generated from a sample collection in the field when samples are subsequently ' .
                             'analyzed in a laboratory.'
        ],
        'modeledperiod' => [
            'name' => 'Modeled Period',
            'description' => 'Data represents simulated conditions during the time period, ' .
                             'and may be used to characterize data generated using a computational model.'
        ],
        'both' => [
            'name' => 'Ground Condition and Modeled Period',
            'description' => 'Both choices apply.'
        ],
    ];

    /**
     * Valid values for self::$topicKeywords.
     *
     * The array keys are the values to be set in self::topicKeywords.
     */
    const TOPIC_KEYWORD_CHOICES = [
        'oceans' => [
            'name' => 'Oceans',
        ],
        'biota' => [
            'name' => 'Biota',
        ],
        'boundries' => [
            'name' => 'Boundries',
        ],
        'climatology' => [
            'name' => 'Climatology/Meteorology/Atmosphere',
        ],
        'economy' => [
            'name' => 'Economy',
        ],
        'elevation' => [
            'name' => 'Elevation',
        ],
        'environment' => [
            'name' => 'Environment',
        ],
        'farming' => [
            'name' => 'Farming',
        ],
        'geoscientificInformation' => [
            'name' => 'Geoscientific Information',
        ],
        'health' => [
            'name' => 'Health',
        ],
        'imagery' => [
            'name' => 'Imagery/Base Maps/Earth Cover',
        ],
        'inlandWaters' => [
            'name' => 'Inland Waters',
        ],
        'location' => [
            'name' => 'Location',
        ],
        'militaryIntelligence' => [
            'name' => 'Military Intelligence',
        ],
        'planning' => [
            'name' => 'Planning/Cadastre',
        ],
        'society' => [
            'name' => 'Society',
        ],
        'structure' => [
            'name' => 'Structure',
        ],
        'transportation' => [
            'name' => 'Transportation',
        ],
        'utilities' => [
            'name' => 'Utilities/Communication',
        ],
    ];

    /**
     * The Dataset this Dataset Submission is attached to.
     *
     * @var Dataset
     *
     * @ORM\ManyToOne(targetEntity="Dataset", inversedBy="datasetSubmissionHistory", cascade={"persist"})
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
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
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NotBlank(
     *     message="The dataset submission title is required."
     * )
     */
    protected $title;

    /**
     * The short title for this Dataset Submission.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NotBlank(
     *     message="The dataset submission short title is required."
     * )
     */
    protected $shortTitle;

    /**
     * The abstract for this Dataset Submission.
     *
     * Legacy DB column: dataset_abstract
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NotBlank(
     *     message="The dataset submission abstract is required."
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
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NotBlank(
     *     message="The dataset submission author list is required."
     * )
     */
    protected $authors;

    /**
     * The Point of Contact for this Dataset Submission.
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="PersonDatasetSubmissionDatasetContact", mappedBy="datasetSubmission", cascade={"persist"}, orphanRemoval=true)
     *
     * @Assert\NotBlank(
     *     message="Dataset contact person required."
     * )
     */
    protected $datasetContacts;

    /**
     * The Point of Contact for the metadata associated with this submission.
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="PersonDatasetSubmissionMetadataContact", mappedBy="datasetSubmission", cascade={"persist"}, orphanRemoval=true)
     *
     * @Assert\NotBlank(
     *     message="Metadata contact person required."
     * )
     */
    protected $metadataContacts;

    /**
     * Whether the dataset has any restrictions.
     *
     * Legacy DB column: access_status
     *
     * @var string
     *
     * @see RESTRICTIONS class constant for valid values.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $restrictions = self::RESTRICTION_NONE;

    /**
     * The DOI for this dataset.
     *
     * Legacy DB column: doi
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $datasetFileTransferType;

    /**
     * The dataset file URI.
     *
     * This specifies the location of the source dataset file and can be a file, http(s), or ftp URI.
     *
     * Legacy DB column: url_data
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $datasetFileUri;

    /**
     * The dataset file transfer status.
     *
     * Legacy DB column: dataset_download_status
     *
     * @var string
     *
     * @see TRANSFER_STATUSES class constant for valid values.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $datasetFileTransferStatus;

    /**
     * The dataset file name.
     *
     * Legacy DB column: dataset_filename
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $datasetFileMd5Hash;

    /**
     * The dataset file sha1 hash.
     *
     * Legacy DB column: fs_sha1_hash
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $datasetFileSha1Hash;

    /**
     * The dataset file sha256 hash.
     *
     * Legacy DB column: fs_sha256_hash
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $metadataFileTransferType;

    /**
     * The metadata file URI.
     *
     * This specifies the location of the source metadata file and can be a file, http(s), or ftp URI.
     *
     * Legacy DB column: url_metadata
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $metadataFileUri;

    /**
     * The metadata file transfer status.
     *
     * Legacy DB column: metadata_dl_status
     *
     * @var string
     *
     * @see TRANSFER_STATUSES class constant for valid values.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $metadataFileTransferStatus;

    /**
     * The metadata file name.
     *
     * Legacy DB column: dataset_metadata
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $metadataFileName;

    /**
     * The metadata file sha256 hash.
     *
     * Legacy DB column: metadata_file_hash
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $metadataStatus = self::METADATA_STATUS_NONE;

    /**
     * The reference date for this dataset.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Assert\NotBlank(
     *     message="The dataset submission reference date field is required."
     * )
     */
    protected $referenceDate;

    /**
     * The type of the reference date for this dataset.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @see REFERENCE_DATE_CHOICES class constant for valid values.
     *
     * @Assert\NotBlank(
     *     message="The dataset submission reference date type field is required."
     * )
     */
    protected $referenceDateType;

    /**
     * The purpose of this dataset.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NotBlank(
     *     message="The dataset submission purpose field is required."
     * )
     */
    protected $purpose;

    /**
     * Supplemental information - parameters.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NotBlank(
     *     message="The dataset submission data parameters/units field is required."
     * )
     */
    protected $suppParams;

    /**
     * Supplemental information - methods.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $suppMethods;

    /**
     * Supplemental information - instruments.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $suppInstruments;

    /**
     * Supplemental information - sample scales and rates.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $suppSampScalesRates;

    /**
     * Supplemental information - error analysis.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $suppErrorAnalysis;

    /**
     * Supplemental information - provenance and historical references.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $suppProvenance;

    /**
     * Theme keywords describing this dataset.
     *
     * @var string $themeKeywords Freetext theme keywords describing dataset.
     *
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @Assert\NotBlank(
     *     message="The dataset submission keyword(s) field is required."
     * )
     */
    protected $themeKeywords;

    /**
     * Place keywords describing this dataset.
     *
     * @var string $placeKeywords Freetext place keywords describing the dataset.
     *
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $placeKeywords;

    /**
     * Topic keywords describing this dataset.
     *
     * @var string $topicKeywords Keywords describing dataset from NOAA list.
     *
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @Assert\NotBlank(
     *     message="The dataset submission topic keyword(s) field is required."
     * )
     */
    protected $topicKeywords;

    /**
     * Spatial extent as WKT.
     *
     * @var string $spatialExtent Geographic footprint as well-known text.
     *
     * @ORM\Column(type="geometry", options={"srid"=4326}, nullable=true)
     */
    protected $spatialExtent;

    /**
     * Temporal extent description.
     *
     * @var string $temporalExtentDesc Description of time period as: 'ground condition', 'modeled period', or 'both'.
     *
     * @see TEMPORAL_EXTENT_DESCRIPTIONS class constant for valid values.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $temporalExtentDesc;

    /**
     * The temporal beginning position (date).
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $temporalExtentBeginPosition;

    /**
     * The temporal ending position (date).
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $temporalExtentEndPosition;

    /**
     * Constructor.
     *
     * Initializes collections to empty collections.
     */
    public function __construct()
    {
        $this->datasetContacts = new ArrayCollection;
        $this->metadataContacts = new ArrayCollection;
    }

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
        $this->updateDatasetSubmissionStatus();
        $this->updateMetadataStatus();
        $this->updateAvailabilityStatus();
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
     * @throws \InvalidArgumentException When $sequence is not an integer.
     *
     * @return void
     */
    public function setSequence($sequence)
    {
        if ('integer' !== gettype($sequence)) {
            throw new \InvalidArgumentException('Sequence must be an integer');
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
     * Set the short title for this Dataset Submission.
     *
     * @param string $shortTitle The short title for this Dataset Submission.
     *
     * @return void
     */
    public function setShortTitle($shortTitle)
    {
        $this->shortTitle = $shortTitle;
    }

    /**
     * Get the short title for this Dataset Submission.
     *
     * @return string
     */
    public function getShortTitle()
    {
        return $this->shortTitle;
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
     * Adder for dataset contact.
     *
     * @param PersonDatasetSubmissionDatasetContact $datasetContact Single object to be added.
     *
     * @access public
     *
     * @throws \InvalidArgumentException When $datasetContact is not a PersonDatasetSubmissionDatasetContact.
     *
     * @return void
     */
    public function addDatasetContact(PersonDatasetSubmissionDatasetContact $datasetContact)
    {
        if (!$datasetContact instanceof PersonDatasetSubmissionDatasetContact) {
            throw new \InvalidArgumentException('Non-PersonDatasetSubmissionDatasetContact found.');
        }
        $datasetContact->setDatasetSubmission($this);
        $this->datasetContacts->add($datasetContact);
    }

    /**
     * Remover for dataset contact.
     *
     * @param PersonDatasetSubmissionDatasetContact $datasetContact Single object to be removed.
     *
     * @access public
     *
     * @throws \InvalidArgumentException When $datasetContact is not a PersonDatasetSubmissionDatasetContact.
     *
     * @return void
     */
    public function removeDatasetContact(PersonDatasetSubmissionDatasetContact $datasetContact)
    {
        if (!$datasetContact instanceof PersonDatasetSubmissionDatasetContact) {
            throw new \InvalidArgumentException('Non-PersonDatasetSubmissionDatasetContact found.');
        }
        $this->datasetContacts->removeElement($datasetContact);
    }

    /**
     * Getter of datasetContacts.
     *
     * @access public
     *
     * @return \Doctrine\Common\Collections\Collection Collection containing PersonDatasetSubmissionDatasetContacts
     */
    public function getDatasetContacts()
    {
        return $this->datasetContacts;
    }

    /**
     * Adder for metadata contact.
     *
     * @param PersonDatasetSubmissionMetadataContact $metadataContact Single object to be added.
     *
     * @access public
     *
     * @throws \InvalidArgumentException When $metadataContact is not a PersonDatasetSubmissionMetadataContact.
     *
     * @return void
     */
    public function addMetadataContact(PersonDatasetSubmissionMetadataContact $metadataContact)
    {
        if (!$metadataContact instanceof PersonDatasetSubmissionMetadataContact) {
            throw new \InvalidArgumentException('Non-PersonDatasetSubmissionMetadataContact found.');
        }
        $metadataContact->setDatasetSubmission($this);
        $this->metadataContacts->add($metadataContact);
    }

    /**
     * Remover for metadata contact.
     *
     * @param PersonDatasetSubmissionMetadataContact $metadataContact Single object to be removed.
     *
     * @access public
     *
     * @throws \InvalidArgumentException When $metadataContact is not a PersonDatasetSubmissionMetadataContact.
     *
     * @return void
     */
    public function removeMetadataContact(PersonDatasetSubmissionMetadataContact $metadataContact)
    {
        if (!$metadataContact instanceof PersonDatasetSubmissionMetadataContact) {
            throw new \InvalidArgumentException('Non-PersonDatasetSubmissionMetadataContact found.');
        }
        $this->metadataContacts->removeElement($metadataContact);
    }

    /**
     * Getter of metadataContacts.
     *
     * @access public
     *
     * @return \Doctrine\Common\Collections\Collection Collection containing PersonDatasetSubmissionMetadataContacts
     */
    public function getMetadataContacts()
    {
        return $this->metadataContacts;
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
        $this->updateAvailabilityStatus();
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
        if ($this->getDataset() instanceof Dataset) {
            $this->getDataset()->updateDoi();
        }
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
     * Set the dataset file URI.
     *
     * @param string $datasetFileUri The dataset file URI.
     *
     * @return void
     */
    public function setDatasetFileUri($datasetFileUri)
    {
        $this->datasetFileUri = $datasetFileUri;
        $this->updateDatasetSubmissionStatus();
    }

    /**
     * Get the dataset file URI.
     *
     * @return string
     */
    public function getDatasetFileUri()
    {
        return $this->datasetFileUri;
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
        $this->updateAvailabilityStatus();
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
     * Set the metadata file URI.
     *
     * @param string $metadataFileUri The metadata file URI.
     *
     * @return void
     */
    public function setMetadataFileUri($metadataFileUri)
    {
        $this->metadataFileUri = $metadataFileUri;
    }

    /**
     * Get the metadata file URI.
     *
     * @return string
     */
    public function getMetadataFileUri()
    {
        return $this->metadataFileUri;
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
        $this->updateMetadataStatus();
        $this->updateAvailabilityStatus();
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
     * This will return null if the dataset is not set or the dataset does not have an UDI.
     *
     * @return string|null
     */
    public function getDatasetSubmissionId()
    {
        // If the dataset is not set or the dataset does not have an UDI.
        if (!$this->dataset instanceof Dataset or null === $this->dataset->getUdi()) {
            return null;
        }
        return $this->dataset->getUdi() . '.' . sprintf('%03d', $this->sequence);
    }

    /**
     * Set the dataset's reference date.
     *
     * @param \DateTime $referenceDate The dataset's reference date.
     *
     * @return void
     */
    public function setReferenceDate(\DateTime $referenceDate)
    {
        $this->referenceDate = $referenceDate;
    }

    /**
     * Get the dataset's reference date.
     *
     * @return \DateTime
     */
    public function getReferenceDate()
    {
        return $this->referenceDate;
    }

    /**
     * Set reference date type.
     *
     * @param string $referenceDateType The designated type of dataset reference.
     *
     * @see REFERENCE_DATE_TYPES class constant for possible values.
     *
     * @throws \InvalidArgumentException When $referenceDateType is not a valid value.
     *
     * @return void
     */
    public function setReferenceDateType($referenceDateType)
    {
        if (!array_key_exists($referenceDateType, static::REFERENCE_DATE_TYPES)) {
            throw new \InvalidArgumentException("$referenceDateType is not a valid value for DatasetSubmission::REFERENCE_DATE_TYPES");
        }
    }

    /**
     * Get the type of reference date associated with this submission.
     *
     * @return string
     */
    public function getReferenceDateType()
    {
        return $this->referenceDateType;
    }

    /**
     * Sets the purpose.
     *
     * @param string $purpose The purpose of the dataset.
     *
     * @return void
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;
    }

    /**
     * Gets the purpose.
     *
     * @return string
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * Sets the Supplemental Information - Data Parameters and Units.
     *
     * @param string $suppParams Supplemental data parameters and units.
     *
     * @return void
     */
    public function setSuppParams($suppParams)
    {
        $this->suppParams = $suppParams;
    }

    /**
     * Gets the Supplemental Information - Data Parameters and Units.
     *
     * @return string
     */
    public function getSuppParams()
    {
        return $this->suppParams;
    }

    /**
     * Sets the Supplemental Information - Methods.
     *
     * @param string $suppMethods Supplemental data methods.
     *
     * @return void
     */
    public function setSuppMethods($suppMethods)
    {
        $this->suppMethods = $suppMethods;
    }

    /**
     * Gets the Supplemental Information - Methods.
     *
     * @return string
     */
    public function getSuppMethods()
    {
        return $this->suppMethods;
    }

    /**
     * Sets the Supplemental Information - Instruments.
     *
     * @param string $suppInstruments Supplemental data - instruments.
     *
     * @return void
     */
    public function setSuppInstruments($suppInstruments)
    {
        $this->suppInstruments = $suppInstruments;
    }

    /**
     * Gets the Supplemental Information - Instruments.
     *
     * @return string
     */
    public function getSuppInstruments()
    {
        return $this->suppInstruments;
    }

    /**
     * Sets the Supplemental Information - sampling scales and rates.
     *
     * @param string $suppSampScalesRates Supplemental data - sampling scales and rates.
     *
     * @return void
     */
    public function setSuppSampScalesRates($suppSampScalesRates)
    {
        $this->suppSampScalesRates = $suppSampScalesRates;
    }

    /**
     * Gets the Supplemental Information - sampling scales and rates.
     *
     * @return string
     */
    public function getSuppSampScalesRates()
    {
        return $this->suppSampScalesRates;
    }

    /**
     * Sets the Supplemental Information - error analysis.
     *
     * @param string $suppErrorAnalysis Supplemental data - error analysis.
     *
     * @return void
     */
    public function setSuppErrorAnalysis($suppErrorAnalysis)
    {
        $this->suppErrorAnalysis = $suppErrorAnalysis;
    }

    /**
     * Gets the Supplemental Information - error analysis.
     *
     * @return string
     */
    public function getSuppErrorAnalysis()
    {
        return $this->suppErrorAnalysis;
    }

    /**
     * Sets the Supplemental Information - provenance and historical references.
     *
     * @param string $suppProvenance Supplemental data - provenance and historical references.
     *
     * @return void
     */
    public function setSuppProvenance($suppProvenance)
    {
        $this->suppProvenance = $suppProvenance;
    }

    /**
     * Gets the Supplemental Information - provenance and historical references.
     *
     * @return string
     */
    public function getSuppProvenance()
    {
        return $this->suppProvenance;
    }

    /**
     * Setter for theme keywords.
     *
     * @param array $themeKeywords Array of keywords.
     *
     * @return void
     */
    public function setThemeKeywords(array $themeKeywords)
    {
        $this->themeKeywords = $themeKeywords;
    }

    /**
     * Getter for theme keywords.
     *
     * @return array
     */
    public function getThemeKeywords()
    {
        return $this->themeKeywords;
    }

    /**
     * Setter for place keywords.
     *
     * @param array $placeKeywords Array of keywords.
     *
     * @return void
     */
    public function setPlaceKeywords(array $placeKeywords)
    {
        $this->placeKeywords = $placeKeywords;
    }

    /**
     * Getter for place keywords.
     *
     * @return array
     */
    public function getPlaceKeywords()
    {
        return $this->placeKeywords;
    }

    /**
     * Setter for topic keywords.
     *
     * @param array $topicKeywords Array of keywords.
     *
     * @see TOPIC_KEYWORD_CHOICES
     *
     * @throws \InvalidArgumentException When $topicKeywords contains invalid value.
     *
     * @return void
     */
    public function setTopicKeywords(array $topicKeywords)
    {
        foreach ($topicKeywords as $keyword) {
            if (!array_key_exists($keyword, static::TOPIC_KEYWORD_CHOICES)) {
                throw new \InvalidArgumentException("$keyword is not a valid value for DatasetSubmission::TOPIC_KEYWORD_CHOICES");
            }
        }
        $this->topicKeywords = $topicKeywords;
    }

    /**
     * Getter for topic keywords.
     *
     * @return array
     */
    public function getTopicKeywords()
    {
        return $this->topicKeywords;
    }

    /**
     * Setter for geographic spatial extent.
     *
     * @param string $spatialExtent Well-Known text of dataset's geometry.
     *
     * @return void
     */
    public function setSpatialExtent($spatialExtent)
    {
        $this->spatialExtent = $spatialExtent;
    }

    /**
     * Getter for geographic spatial extent.
     *
     * @return string As WKT.
     */
    public function getSpatialExtent()
    {
        return $this->spatialExtent;
    }

    /**
     * Setter for dataset's temporal extent description.
     *
     * @param string $temporalExtentDesc Description of temporal extent, either 'ground condition' or 'modeled period'.
     *
     * @throws \InvalidArgumentException If $temporalExtentDesc is not in static::TEMPORAL_EXTENT_DESCRIPTIONS.
     *
     * @return void
     */
    public function setTimePeriodDesc($temporalExtentDesc)
    {
        if (!array_key_exists($temporalExtentDesc, static::TEMPORAL_EXTENT_DESCRIPTIONS)) {
            throw new \InvalidArgumentException("$temporalExtentDesc is not a valid value for DatasetSubmission::TEMPORAL_EXTENT_DESCRIPTIONS");
        }
        $this->temporalExtentDesc = $temporalExtentDesc;
    }

    /**
     * Getter for dataset's temporal extent description.
     *
     * @return string
     */
    public function getTemporalExtentDesc()
    {
        return $this->temporalExtentDesc;
    }

    /**
     * Set the dataset's temporal extent begin position.
     *
     * @param \DateTime $temporalExtentBeginPosition The temporal extent begin position.
     *
     * @return void
     */
    public function setTemporalExtentBeginPosition(\DateTime $temporalExtentBeginPosition)
    {
        $this->temporalExtentBeginPosition = $temporalExtentBeginPosition;
    }

    /**
     * Get the dataset's temporal extent begin position.
     *
     * @return \DateTime
     */
    public function getTemporalExtentBeginPosition()
    {
        return $this->temporalExtentBeginPosition;
    }

    /**
     * Set the dataset's temporal extent end position.
     *
     * @param \DateTime $temporalExtentEndPosition The temporal extent end position.
     *
     * @return void
     */
    public function setTemporalExtentEndPosition(\DateTime $temporalExtentEndPosition)
    {
        $this->temporalExtentEndPosition = $temporalExtentEndPosition;
    }

    /**
     * Get the dataset's temporal extent end position.
     *
     * @return \DateTime
     */
    public function getTemporalExtentEndPosition()
    {
        return $this->temporalExtentEndPosition;
    }

    /**
     * Update the dataset submission status in associated Dataset if a Dataset has been associated.
     *
     * @return void
     */
    protected function updateDatasetSubmissionStatus()
    {
        if ($this->getDataset() instanceof Dataset) {
            if (null === $this->getDatasetFileUri()) {
                $this->getDataset()->setDatasetSubmissionStatus(self::STATUS_INCOMPLETE);
            } else {
                $this->getDataset()->setDatasetSubmissionStatus(self::STATUS_COMPLETE);
            }
        }
    }

    /**
     * Update the metadata status in associated Dataset if a Dataset has been associated.
     *
     * @return void
     */
    protected function updateMetadataStatus()
    {
        if ($this->getDataset() instanceof Dataset) {
            $this->getDataset()->setMetadataStatus($this->getMetadataStatus());
        }
    }

    /**
     * Update the availability status in associated Dataset if a Dataset has been associated.
     *
     * @return void
     */
    protected function updateAvailabilityStatus()
    {
        if (!$this->getDataset() instanceof Dataset) {
            return;
        }
        $availabilityStatus = self::AVAILABILITY_STATUS_NOT_AVAILABLE;
        switch ($this->getDatasetFileTransferStatus()) {
            case self::TRANSFER_STATUS_COMPLETED:
                if ($this->getMetadataStatus() === self::METADATA_STATUS_ACCEPTED) {
                    switch ($this->getRestrictions()) {
                        case self::RESTRICTION_NONE:
                            $availabilityStatus = self::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE;
                            break;
                        case self::RESTRICTION_APPROVAL:
                            $availabilityStatus = self::AVAILABILITY_STATUS_AVAILABLE_WITH_APPROVAL;
                            break;
                        case self::RESTRICTION_RESTRICTED:
                            $availabilityStatus = self::AVAILABILITY_STATUS_RESTRICTED;
                            break;
                    }
                } elseif ($this->getMetadataFileTransferStatus() === self::TRANSFER_STATUS_COMPLETED) {
                    $availabilityStatus = self::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL;
                } else {
                    $availabilityStatus = self::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION;
                }
                break;
            case self::TRANSFER_STATUS_REMOTELY_HOSTED:
                if ($this->getMetadataStatus() === self::METADATA_STATUS_ACCEPTED) {
                    switch ($this->getRestrictions()) {
                        case self::RESTRICTION_NONE:
                            $availabilityStatus = self::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED;
                            break;
                        case self::RESTRICTION_APPROVAL:
                            $availabilityStatus = self::AVAILABILITY_STATUS_AVAILABLE_WITH_APPROVAL_REMOTELY_HOSTED;
                            break;
                        case self::RESTRICTION_RESTRICTED:
                            $availabilityStatus = self::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED;
                            break;
                    }
                } elseif ($this->getMetadataFileTransferStatus() === self::TRANSFER_STATUS_COMPLETED) {
                    $availabilityStatus = self::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL;
                } else {
                    $availabilityStatus = self::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION;
                }
                break;
        }
        $this->getDataset()->setAvailabilityStatus($availabilityStatus);
    }

    /**
     * Gets the valid choices for reference date types.
     *
     * @return array
     */
    public function getReferenceDateTypeChoices()
    {
        return array_flip(
            array_map(
                function ($type) {
                    return $type['name'];
                },
                static::REFERENCE_DATE_TYPES
            )
        );
    }

    /**
     * Gets the valid choices for topic keywords.
     *
     * @return array
     */
    public function getTopicKeywordChoices()
    {
        return array_flip(
            array_map(
                function ($keyword) {
                    return $keyword['name'];
                },
                static::TOPIC_KEYWORD_CHOICES
            )
        );
    }
}
