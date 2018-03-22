<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
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
     * Indicates the algorithm used to produce the MD hash.
     *
     * This is used as an indicator of change in submitted data.
     */
    const SHA256 = 'sha256';

    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Dataset Submission';

    /**
     * Indicates the dataset has no restriction.
     */
    const RESTRICTION_NONE = 'None';

    /**
     * Indicates the dataset may only be downloaded by the submitter.
     */
    const RESTRICTION_RESTRICTED = 'Restricted';

    /**
     * Valid values for $restrictions.
     */
    const RESTRICTIONS = array(
        self::RESTRICTION_NONE => 'None',
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
     * A dataset submission has been submitted, and it is in dataset-review mode.
     */
    const STATUS_IN_REVIEW = 3;

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
     * The dataset is marked as restricted, but is remotely hosted.
     */
    const AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED = 5;

    /**
     * The dataset is marked as publicly available, but is remotely hosted.
     */
    const AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED = 7;

    /**
     * The dataset is restricted.
     */
    const AVAILABILITY_STATUS_RESTRICTED = 8;

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
        'ground condition' => [
            'name' => 'Ground Condition',
            'description' => 'Data represent the actual condition of things on the ground during ' .
                             'the time period specified and may also be used to characterize data ' .
                             'generated from a sample collection in the field when samples are subsequently ' .
                             'analyzed in a laboratory.'
        ],
        'modeled period' => [
            'name' => 'Modeled Period',
            'description' => 'Data represents simulated conditions during the time period, ' .
                             'and may be used to characterize data generated using a computational model.'
        ],
        'ground condition and modeled period' => [
            'name' => 'Ground Condition and Modeled Period',
            'description' => 'Both choices apply.'
        ],
    ];

    /**
     * Valid values for self::$temporalExtentNilReasonType.
     *
     * The array values are the valid values to be set in self::temporalExtentNilReasonType.
     */
    const NILREASON_TYPES = [
        'inapplicable',
        'missing',
        'template',
        'unknown',
        'withheld'
    ];

    /**
     * Valid values for self::$topicKeywords.
     *
     * The array keys are the values to be set in self::topicKeywords.
     */
    const TOPIC_KEYWORDS = [
        'oceans' => [
            'name' => 'Oceans',
            'description' => 'features and characteristics of salt water bodies (excluding inland waters). Examples: tides, tidal waves, coastal information, reefs',
        ],
        'biota' => [
            'name' => 'Biota',
            'description' => 'flora and/or fauna in natural environment. Examples: wildlife, vegetation, biological sciences, ecology, wilderness, sealife, wetlands, habitat',
        ],
        'boundaries' => [
            'name' => 'Boundaries',
            'description' => 'legal land descriptions. Examples: political and administrative boundaries',
        ],
        'climatologyMeteorologyAtmosphere' => [
            'name' => 'Climatology/Meteorology/Atmosphere',
            'description' => 'processes and phenomena of the atmosphere. Examples: cloud cover, weather, climate, atmospheric conditions, climate change, precipitation',
        ],
        'economy' => [
            'name' => 'Economy',
            'description' => 'economic activities, conditions and employment. Examples: production, labour, revenue, commerce, industry, tourism and ecotourism, forestry, fisheries, commercial or subsistence hunting, exploration and exploitation of resources such as minerals, oil and gas',
        ],
        'elevation' => [
            'name' => 'Elevation',
            'description' => 'height above or below sea level. Examples: altitude, bathymetry, digital elevation models, slope, derived products',
        ],
        'environment' => [
            'name' => 'Environment',
            'description' => 'environmental resources, protection and conservation. Examples: environmental pollution, waste storage and treatment, environmental impact assessment, monitoring environmental risk, nature reserves, landscape',
        ],
        'farming' => [
            'name' => 'Farming',
            'description' => 'rearing of animals and/or cultivation of plants. Examples: agriculture, irrigation, aquaculture, plantations, herding, pests and diseases affecting crops and livestock',
        ],
        'geoscientificInformation' => [
            'name' => 'Geoscientific Information',
            'description' => 'information pertaining to earth sciences. Examples: geophysical features and processes, geology, minerals, sciences dealing with the composition, structure and origin of the earth s rocks, risks of earthquakes, volcanic activity, landslides, gravity information, soils, permafrost, hydrogeology, erosion',
        ],
        'health' => [
            'name' => 'Health',
            'description' => 'health, health services, human ecology, and safety. Examples: disease and illness, factors affecting health, hygiene, substance abuse, mental and physical health, health services',
        ],
        'imageryBaseMapsEarthCover' => [
            'name' => 'Imagery/Base Maps/Earth Cover',
            'description' => 'base maps. Examples: land cover, topographic maps, imagery, unclassified images, annotations',
        ],
        'inlandWaters' => [
            'name' => 'Inland Waters',
            'description' => 'inland water features, drainage systems and their characteristics. Examples: rivers and glaciers, salt lakes, water utilization plans, dams, currents, floods, water quality, hydrographic charts',
        ],
        'location' => [
            'name' => 'Location',
            'description' => 'positional information and services. Examples: addresses, geodetic networks, control points, postal zones and services, place names',
        ],
        'intelligenceMilitary' => [
            'name' => 'Military Intelligence',
            'description' => 'military bases, structures, activities. Examples: barracks, training grounds, military transportation, information collection',
        ],
        'planningCadastre' => [
            'name' => 'Planning/Cadastre',
            'description' => 'information used for appropriate actions for future use of the land. Examples: land use maps, zoning maps, cadastral surveys, land ownership',
        ],
        'society' => [
            'name' => 'Society',
            'description' => 'characteristics of society and cultures. Examples: settlements, anthropology, archaeology, education, traditional beliefs, manners and customs, demographic data, recreational areas and activities, social impact assessments, crime and justice, census information',
        ],
        'structure' => [
            'name' => 'Structure',
            'description' => 'man-made construction. Examples: buildings, museums, churches, factories, housing, monuments, shops, towers',
        ],
        'transportation' => [
            'name' => 'Transportation',
            'description' => 'means and aids for conveying persons and/or goods. Examples: roads, airports/airstrips, shipping routes, tunnels, nautical charts, vehicle or vessel location, aeronautical charts, railways',
        ],
        'utilitiesCommunication' => [
            'name' => 'Utilities/Communication',
            'description' => 'energy, water and waste systems and communications infrastructure and services. Examples: hydroelectricity, geothermal, solar and nuclear sources of energy, water purification and distribution, sewage collection and disposal, electricity and gas distribution, data communication, telecommunication, radio, communication networks',
        ],
    ];

    /**
     * Indicates the dataset submission is in endReview state.
     */
    const DATASET_END_REVIEW = 'endReview';

    /**
     * Indicates the dataset submission is in acceptReview state.
     */
    const DATASET_ACCEPT_REVIEW = 'acceptReview';

    /**
     * Indicates the dataset submission is in request revisions (back to submitter) state.
     */
    const DATASET_REQUEST_REVISIONS = 'requestRevisions';

    /**
     * Status of this Dataset Submission.
     *
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $status = self::STATUS_INCOMPLETE;

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
     * The review for this Dataset Submission.
     *
     * @var DatasetSubmissionReview
     *
     * @ORM\OneToOne(targetEntity="DatasetSubmissionReview", mappedBy="datasetSubmission", cascade={"persist"})
     */
    protected $datasetSubmissionReview;

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
     * The Point of Contact Name for this Dataset Submission.
     *
     * Legacy DB column: dataset_poc_name
     *
     * @var string
     *
     * @deprecated Replaced by self::$datasetContacts.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $pointOfContactName;

    /**
     * The Point of Contact E-Mail for this Dataset Submission.
     *
     * Legacy DB column: dataset_poc_email
     *
     * @var string
     *
     * @deprecated Replaced by self::$datasetContacts.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $pointOfContactEmail;

    /**
     * The Point of Contact for this Dataset Submission.
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="PersonDatasetSubmissionDatasetContact", mappedBy="datasetSubmission", cascade={"persist"}, orphanRemoval=true)
     *
     * @ORM\OrderBy({"primaryContact" = "DESC", "creationTimeStamp" = "ASC"})
     *
     * @Assert\Count(
     *      min = "1",
     *      minMessage="A Dataset contact person is required."
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
     * @ORM\OrderBy({"creationTimeStamp" = "ASC"})
     *
     * @Assert\Count(
     *      min = "1",
     *      minMessage="A Metadata contact person is required."
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
     * @ORM\Column(type="text", nullable=false)
     *
     * @Assert\NotBlank(
     *     message="The dataset submission restrictions must be set."
     * )
     */
    protected $restrictions = self::RESTRICTION_NONE;

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
     *
     * @Assert\NotBlank(
     *     message="The dataset submission dataset file transfer type must be set."
     * )
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
     *
     * @Assert\NotBlank(
     *     message="The dataset submission must include a dataset file."
     * )
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
     * @ORM\Column(type="text", nullable=false)
     */
    protected $metadataStatus = self::METADATA_STATUS_NONE;

    /**
     * The reference date for this dataset.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz", nullable=true)
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
     * @var array
     *
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @Assert\NotBlank(
     *     message="The dataset submission theme keyword(s) field is required."
     * )
     */
    protected $themeKeywords = array();

    /**
     * Place keywords describing this dataset.
     *
     * @var array
     *
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $placeKeywords = array();

    /**
     * Topic keywords describing this dataset.
     *
     * @var array
     *
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @Assert\NotBlank(
     *     message="The dataset submission topic keyword(s) field is required."
     * )
     */
    protected $topicKeywords = array();

    /**
     * Spatial extent.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $spatialExtent;

    /**
     * Spatial extent description.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $spatialExtentDescription;

    /**
     * Temporal extent description.
     *
     * @var string
     *
     * @see TEMPORAL_EXTENT_DESCRIPTIONS class constant for valid values.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $temporalExtentDesc;

    /**
     * The temporal beginning position (date).
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    protected $temporalExtentBeginPosition;

    /**
     * The temporal ending position (date).
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    protected $temporalExtentEndPosition;

    /**
     * Nilreason type for datasets which do not spatial extent.
     *
     * @var string
     *
     * @see NILREASON_TYPES class constant for valid values.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $temporalExtentNilReasonType;

    /**
     * The name of the format the data is distributed in.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $distributionFormatName;

    /**
     * The technique used to decompress the dataset.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $fileDecompressionTechnique;

    /**
     * When this Dataset Submission was submitted.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    protected $submissionTimeStamp;

    /**
     * The Person who submitted this Dataset Submission.
     *
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Person")
     */
    protected $submitter;

    /**
     * Constructor.
     *
     * Initializes collections to empty collections.
     *
     * @param Entity                                $entity      A DIF or DatasetSubmission to base this DatasetSubmission on.
     * @param PersonDatasetSubmissionDatasetContact $datasetPPOc The dataset's Primary P.O.C., used if creating from a DIF.
     *
     * @throws \Exception When a DIF is passed without a PersonDatasetSubmissionDatasetContact.
     * @throws \Exception When an entity is passed that is not a DIF or DatasetSubmission.
     */
    public function __construct(Entity $entity, PersonDatasetSubmissionDatasetContact $datasetPPOc = null)
    {
        $this->datasetContacts = new ArrayCollection;
        $this->metadataContacts = new ArrayCollection;
        if ($entity instanceof DIF) {
            if (null === $datasetPPOc) {
                throw new \Exception('Constructor requires PersonDatasetSubmissionDatasetContact if passed a DIF entity');
            }
            // Populate from DIF
            $this->setDataset($entity->getDataset());
            $this->setTitle($entity->getTitle());
            $this->setAbstract($entity->getAbstract());
            $this->setSuppParams($entity->getVariablesObserved());
            $this->setSpatialExtent($entity->getSpatialExtentGeometry());
            $this->setSpatialExtentDescription($entity->getSpatialExtentDescription());
            // Add DIF primary point of contact to collection and designate as primary dataset contact.
            // DIF's primaryPointOfContact is required by DIF.
            $datasetPPOc->setPerson($entity->getPrimaryPointOfContact());
            $datasetPPOc->setRole(PersonDatasetSubmissionDatasetContact::getRoleChoices()['Point of Contact']);
            $datasetPPOc->setPrimaryContact(true);
            $this->addDatasetContact($datasetPPOc);
            // Add metadata contact.
            $metadataContact = new PersonDatasetSubmissionMetadataContact();
            $this->addMetadataContact($metadataContact);
            // Add additional point of contact if DIF has secondaryPointOfContact.
            if ($entity->getSecondaryPointOfContact()) {
                $newDatasetContact = new PersonDatasetSubmissionDatasetContact();
                $newDatasetContact->setRole(PersonDatasetSubmissionDatasetContact::getRoleChoices()['Point of Contact']);
                $newDatasetContact->setPerson($entity->getSecondaryPointOfContact());
                $newDatasetContact->setPrimaryContact(false);
                $this->addDatasetContact($newDatasetContact);
            }
        } elseif ($entity instanceof DatasetSubmission) {
            // Increment the sequence.
            $this->setSequence($entity->getSequence() + 1);
            // Populate from original Dataset Submission.
            $this->setDataset($entity->getDataset());
            $this->setTitle($entity->getTitle());
            $this->setShortTitle($entity->getShortTitle());
            $this->setAbstract($entity->getAbstract());
            $this->setAuthors($entity->getAuthors());
            $this->setRestrictions($entity->getRestrictions());
            $this->setDatasetFileTransferType($entity->getDatasetFileTransferType());
            $this->setDatasetFileUri($entity->getDatasetFileUri());
            $this->setDatasetFileTransferStatus($entity->getDatasetFileTransferStatus());
            $this->setDatasetFileName($entity->getDatasetFileName());
            $this->setDatasetFileSize($entity->getDatasetFileSize());
            $this->setDatasetFileMd5Hash($entity->getDatasetFileMd5Hash());
            $this->setDatasetFileSha1Hash($entity->getDatasetFileSha1Hash());
            $this->setDatasetFileSha256Hash($entity->getDatasetFileSha256Hash());
            $this->setMetadataFileTransferType($entity->getMetadataFileTransferType());
            $this->setMetadataFileUri($entity->getMetadataFileUri());
            $this->setMetadataFileTransferStatus($entity->getMetadataFileTransferStatus());
            $this->setMetadataFileName($entity->getMetadataFileName());
            $this->setMetadataFileSha256Hash($entity->getMetadataFileSha256Hash());
            $this->setMetadataStatus($entity->getMetadataStatus());
            $this->setReferenceDate($entity->getReferenceDate());
            $this->setReferenceDateType($entity->getReferenceDateType());
            $this->setPurpose($entity->getPurpose());
            $this->setSuppParams($entity->getSuppParams());
            $this->setSuppMethods($entity->getSuppMethods());
            $this->setSuppInstruments($entity->getSuppInstruments());
            $this->setSuppSampScalesRates($entity->getSuppSampScalesRates());
            $this->setSuppErrorAnalysis($entity->getSuppErrorAnalysis());
            $this->setSuppProvenance($entity->getSuppProvenance());
            $this->setThemeKeywords($entity->getThemeKeywords());
            $this->setPlaceKeywords($entity->getPlaceKeywords());
            $this->setTopicKeywords($entity->getTopicKeywords());
            $this->setSpatialExtent($entity->getSpatialExtent());
            $this->setSpatialExtentDescription($entity->getSpatialExtentDescription());
            $this->setTemporalExtentDesc($entity->getTemporalExtentDesc());
            $this->setTemporalExtentBeginPosition($entity->getTemporalExtentBeginPosition());
            $this->setTemporalExtentEndPosition($entity->getTemporalExtentEndPosition());
            $this->setTemporalExtentNilReasonType($entity->getTemporalExtentNilReasonType());
            $this->setDistributionFormatName($entity->getDistributionFormatName());
            $this->setFileDecompressionTechnique($entity->getFileDecompressionTechnique());

            //Submitter should always be the user who has submitted the dataset.
            if (!in_array($entity->getMetadataStatus(), [ self::METADATA_STATUS_NONE, self::METADATA_STATUS_BACK_TO_SUBMITTER])) {
                $this->submitter = $entity->getSubmitter();
                $this->submissionTimeStamp = $entity->getSubmissionTimeStamp();
            }
            // Copy the original Dataset Submission's dataset contacts.
            foreach ($entity->getDatasetContacts() as $datasetContact) {
                $newDatasetContact = new PersonDatasetSubmissionDatasetContact();
                $newDatasetContact->setRole($datasetContact->getRole());
                $newDatasetContact->setPerson($datasetContact->getPerson());
                $newDatasetContact->setPrimaryContact($datasetContact->isPrimaryContact());
                $this->addDatasetContact($newDatasetContact);
            }
            // Copy the original Dataset Submission's metadata contacts.
            foreach ($entity->getMetadataContacts() as $metadataContact) {
                $newMetadataContact = new PersonDatasetSubmissionMetadataContact();
                $newMetadataContact->setRole($metadataContact->getRole());
                $newMetadataContact->setPerson($metadataContact->getPerson());
                $this->addMetadataContact($newMetadataContact);
            }
        } else {
            throw new \Exception('Class constructor requires a DIF or a DatasetSubmission. A ' . get_class($entity) . ' was passed.');
        }
    }

    /**
     * This validator class enforces spatial extent Pelagos requirements.
     *
     * @param ExecutionContextInterface $context Validation context.
     *
     * @Assert\Callback
     *
     * @return void
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (null !== $this->spatialExtent) {
            if (null === $this->temporalExtentDesc) {
                $context->buildViolation('Since a spatial extent is present, this submission must ' .
                    'include a time period description.')
                    ->atPath('temporalExtentDesc')
                    ->addViolation();
            }

            if (!($this->temporalExtentBeginPosition instanceof \DateTime)) {
                $context->buildViolation('Since a spatial extent is present, this submission must ' .
                    'include a start date.')
                    ->atPath('temporalExtentBeginPosition')
                    ->addViolation();
            }

            if (!($this->temporalExtentEndPosition instanceof \DateTime)) {
                $context->buildViolation('Since a spatial extent is present, this submission must ' .
                    'include a end date.')
                    ->atPath('temporalExtentEndPosition')
                    ->addViolation();
            }

        } else {
            if (null === $this->spatialExtentDescription) {
                $context->buildViolation('You must provide either a spatial extent or a spatial extent description.')
                    ->atPath('spatialExtent')
                    ->addViolation();
            }
        }
    }

    /**
     * Clone Handler.
     *
     * Set and correct attributes when cloned.
     *
     * @return void
     */
    public function __clone()
    {
        $this->setId(null);
        $this->setCreationTimeStamp(null);
        if ($this->getStatus() === self::STATUS_COMPLETE) {
            $this->status = self::STATUS_INCOMPLETE;
        }
    }

    /**
     * Get the choice list for restrictions.
     *
     * @return array
     */
    public static function getRestrictionsChoices()
    {
        return array_flip(static::RESTRICTIONS);
    }

    public static function getNilReasonTypes()
    {
        return array_flip(self::NILREASON_TYPES);
    }
    /**
     * Submit this Dataset Submission.
     *
     * @param Person $submitter The submitter.
     *
     * @return void
     */
    public function submit(Person $submitter)
    {
        $this->status = self::STATUS_COMPLETE;
        $this->metadataStatus = self::METADATA_STATUS_SUBMITTED;
        $this->getDataset()->setDatasetSubmission($this);
        $this->submissionTimeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->submitter = $submitter;
        $metadataContact = $this->getMetadataContacts()->first();
        if (!$metadataContact instanceof PersonDatasetSubmissionMetadataContact) {
            $metadataContact = new PersonDatasetSubmissionMetadataContact();
            $metadataContact->setRole('pointOfContact');
            $metadataContact->setPerson($submitter);
            $this->addMetadataContact($metadataContact);
        } else {
            $metadataContact->setRole('pointOfContact');
            $metadataContact->setPerson($submitter);
        }
    }

    /**
     * Triggers the respective event for Dataset Submission Review.
     *
     * @param Person $reviewer  The person who ends/accepts the review.
     * @param string $eventName The event which triggers this method ("endReview" or "acceptReview").
     *
     * @return void
     */
    public function reviewEvent(Person $reviewer, $eventName)
    {
        switch (true) {
            case ($eventName === self::DATASET_END_REVIEW):
                //Setting the status to in-review.
                $this->status = self::STATUS_IN_REVIEW;
                $this->metadataStatus = self::METADATA_STATUS_IN_REVIEW;
                break;
            case ($eventName === self::DATASET_ACCEPT_REVIEW):
                //Setting the status to in-review.
                $this->status = self::STATUS_COMPLETE;
                $this->metadataStatus = self::METADATA_STATUS_ACCEPTED;
                $this->getDataset()->setDatasetSubmission($this);
                break;
            case ($eventName === self::DATASET_REQUEST_REVISIONS):
                $this->status = self::STATUS_COMPLETE;
                $this->metadataStatus = self::METADATA_STATUS_BACK_TO_SUBMITTER;
                $this->status = self::STATUS_COMPLETE;
                $this->getDataset()->setDatasetSubmission($this);
                break;
        }

        $datasetSubmissionReview = $this->getDatasetSubmissionReview();

        // Setting timestamp when review is ended.
        $datasetSubmissionReview->setReviewEndDateTime(new \DateTime('now', new \DateTimeZone('UTC')));

        // Setting review ended by person.
        $datasetSubmissionReview->setReviewEndedBy($reviewer);
    }

    /**
     * Get the status of this dataset submission.
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the Dataset Submission Review for this Dataset Submission.
     *
     * @param DatasetSubmissionReview $datasetSubmissionReview The Review for this Dataset Submission.
     *
     * @return void
     */
    public function setDatasetSubmissionReview(DatasetSubmissionReview $datasetSubmissionReview)
    {
        $this->datasetSubmissionReview = $datasetSubmissionReview;
    }

    /**
     * Get the DataseSubmision review collection.
     *
     * @return DatasetSubmissionReview
     */
    public function getDatasetSubmissionReview()
    {
        return $this->datasetSubmissionReview;
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
     * @return void
     */
    public function addDatasetContact(PersonDatasetSubmissionDatasetContact $datasetContact)
    {
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
     * @return void
     */
    public function removeDatasetContact(PersonDatasetSubmissionDatasetContact $datasetContact)
    {
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
     * @return void
     */
    public function addMetadataContact(PersonDatasetSubmissionMetadataContact $metadataContact)
    {
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
     * @return void
     */
    public function removeMetadataContact(PersonDatasetSubmissionMetadataContact $metadataContact)
    {
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
     * This is as emulated getter for the previous pointOfContactName attribute.
     *
     * This returns the "last, first" name of the first of the datasetContacts collection.
     *
     * @access public
     *
     * @deprecated The self::$pointOfContactName property is deprecated and replaced by self::$datasetContacts.
     *
     * @see self::getDatasetContacts()
     *
     * @return string
     */
    public function getPointOfContactName()
    {
        if ($this->getDatasetContacts()->isEmpty()) {
            if (property_exists(self::class, 'pointOfContactName')) {
                return $this->pointOfContactName;
            }
            return null;
        }
        $contactPerson = $this->getDatasetContacts()->first()->getPerson();
        if (!$contactPerson instanceof Person) {
            return null;
        }
        return $contactPerson->getLastName() . ', ' . $contactPerson->getFirstName();
    }

    /**
     * This is as emulated getter for the previous pointOfContactEmail attribute.
     *
     * This returns the email address of the first of the datasetContacts collection.
     *
     * @access public
     *
     * @deprecated The self::$pointOfContactEmail property is deprecated and replaced by self::$datasetContacts.
     *
     * @see self::getDatasetContacts()
     *
     * @return string
     */
    public function getPointOfContactEmail()
    {
        if ($this->getDatasetContacts()->isEmpty()) {
            if (property_exists(self::class, 'pointOfContactEmail')) {
                return $this->pointOfContactEmail;
            }
            return null;
        }
        $contactPerson = $this->getDatasetContacts()->first()->getPerson();
        if (!$contactPerson instanceof Person) {
            return null;
        }
        return $contactPerson->getEmailAddress();
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
     * @param \DateTime|null $referenceDate The dataset's reference date.
     *
     * @return void
     */
    public function setReferenceDate(\DateTime $referenceDate = null)
    {
        if ($referenceDate != null) {
            $referenceDate->setTimeZone(new \DateTimeZone('UTC'));
        }
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
        if (null !== $referenceDateType and !array_key_exists($referenceDateType, static::REFERENCE_DATE_TYPES)) {
            throw new \InvalidArgumentException("'$referenceDateType' is not a valid value for referenceDateType");
        }
        $this->referenceDateType = $referenceDateType;
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
        $this->themeKeywords = $this->filterArrayBlanks($themeKeywords);
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
        $this->placeKeywords = $this->filterArrayBlanks($placeKeywords);
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
     * @see TOPIC_KEYWORDS
     *
     * @throws \InvalidArgumentException When $topicKeywords contains invalid value.
     *
     * @return void
     */
    public function setTopicKeywords(array $topicKeywords)
    {
        foreach ($topicKeywords as $keyword) {
            if (!array_key_exists($keyword, static::TOPIC_KEYWORDS)) {
                throw new \InvalidArgumentException("'$keyword' is not a valid value for topicKeywords");
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
     * Setter for spatial extent description.
     *
     * @param string $spatialExtentDescription Description of spatial extent.
     *
     * @return void
     */
    public function setSpatialExtentDescription($spatialExtentDescription)
    {
        $this->spatialExtentDescription = $spatialExtentDescription;
    }

    /**
     * Getter for spatial extent description.
     *
     * @return string
     */
    public function getSpatialExtentDescription()
    {
        return $this->spatialExtentDescription;
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
    public function setTemporalExtentDesc($temporalExtentDesc)
    {
        if (null !== $temporalExtentDesc and !array_key_exists($temporalExtentDesc, static::TEMPORAL_EXTENT_DESCRIPTIONS)) {
            throw new \InvalidArgumentException("'$temporalExtentDesc' is not a valid value for temporalExtentDesc");
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
     * @param \DateTime|null $temporalExtentBeginPosition The temporal extent begin position.
     *
     * @return void
     */
    public function setTemporalExtentBeginPosition(\DateTime $temporalExtentBeginPosition = null)
    {
        if ($temporalExtentBeginPosition != null) {
            $temporalExtentBeginPosition->setTimeZone(new \DateTimeZone('UTC'));
        }
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
     * @param \DateTime|null $temporalExtentEndPosition The temporal extent end position.
     *
     * @return void
     */
    public function setTemporalExtentEndPosition(\DateTime $temporalExtentEndPosition = null)
    {
        if ($temporalExtentEndPosition != null) {
            $temporalExtentEndPosition->setTimeZone(new \DateTimeZone('UTC'));
        }
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
     * Set the distribution format name.
     *
     * @param string $distributionFormatName The distribution format name.
     *
     * @return void
     */
    public function setDistributionFormatName($distributionFormatName)
    {
        $this->distributionFormatName = $distributionFormatName;
    }

    /**
     * Get the distribution format name.
     *
     * @return string
     */
    public function getDistributionFormatName()
    {
        return $this->distributionFormatName;
    }

    /**
     * Set the file decompression technique.
     *
     * @param string $fileDecompressionTechnique The file decompression technique.
     *
     * @return void
     */
    public function setFileDecompressionTechnique($fileDecompressionTechnique)
    {
        $this->fileDecompressionTechnique = $fileDecompressionTechnique;
    }

    /**
     * Get the file decompression technique.
     *
     * @return string
     */
    public function getFileDecompressionTechnique()
    {
        return $this->fileDecompressionTechnique;
    }

    /**
     * Get the submission time stamp.
     *
     * @return \DateTime
     */
    public function getSubmissionTimeStamp()
    {
        return $this->submissionTimeStamp;
    }

    /**
     * Get the submitter.
     *
     * @return Person|null
     */
    public function getSubmitter()
    {
        return $this->submitter;
    }

    /**
     * Update the dataset submission status in associated Dataset if a Dataset has been associated.
     *
     * @return void
     */
    protected function updateDatasetSubmissionStatus()
    {
        if ($this->getDataset() instanceof Dataset) {
            $this->getDataset()->setDatasetSubmissionStatus($this->status);
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
        $this->getDataset()->updateAvailabilityStatus();
    }

    /**
     * Gets the valid choices for reference date types.
     *
     * @return array
     */
    public static function getReferenceDateTypeChoices()
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
    public static function getTopicKeywordsChoices()
    {
        return array_flip(
            array_map(
                function ($keyword) {
                    return $keyword['name'];
                },
                static::TOPIC_KEYWORDS
            )
        );
    }

    /**
     * Gets the valid choices for temporal extent description.
     *
     * @return array
     */
    public static function getTemporalExtentDescChoices()
    {
        return array_flip(
            array_map(
                function ($keyword) {
                    return $keyword['name'];
                },
                static::TEMPORAL_EXTENT_DESCRIPTIONS
            )
        );
    }

    /**
     * Sets the dataset submission status to In Review status.
     *
     * @return void
     */
    public function setDatasetSubmissionReviewStatus()
    {
        $this->status = self::STATUS_IN_REVIEW;
    }

    /**
     * Gets the temporal nilreason type for the dataset.
     *
     * @return string
     */
    public function getTemporalExtentNilReasonType()
    {
        return $this->temporalExtentNilReasonType;
    }

    /**
     * Sets the temporal nilreason type for the dataset.
     *
     * @param string $temporalExtentNilReasonType The nilReason for the temporal extent.
     *
     * @return void
     */
    public function setTemporalExtentNilReasonType($temporalExtentNilReasonType)
    {
        if (!in_array($temporalExtentNilReasonType, self::NILREASON_TYPES)) {
                throw new \InvalidArgumentException("'$temporalExtentNilReasonType' is not a valid value for nilReason types");
        }
        $this->temporalExtentNilReasonType = $temporalExtentNilReasonType;
    }
}
