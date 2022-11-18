<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
 *
 * @ORM\Entity(repositoryClass="App\Repository\DatasetSubmissionRepository")
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
     * Valid values for $datasetFileTransferType.
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
     * Indicates that the dataset submission is set to be processed by the filer.
     */
    const TRANSFER_STATUS_BEING_PROCESSED = 'BeingProcessed';

    /**
     * Valid values for $datasetFileTransferStatus.
     */
    const TRANSFER_STATUSES = array(
        self::TRANSFER_STATUS_NONE => 'Not Yet Transferred',
        self::TRANSFER_STATUS_COMPLETED => 'Transfer Complete',
        self::TRANSFER_STATUS_ERROR => 'Transfer Error',
        self::TRANSFER_STATUS_NEEDS_REVIEW => 'URL Needs Review',
        self::TRANSFER_STATUS_REMOTELY_HOSTED => 'Remotely Hosted',
        self::TRANSFER_STATUS_BEING_PROCESSED => 'Submission is being Processed',
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
        'inapplicable' => [
            'name' => 'Inapplicable',
            'description' => 'Inapplicable'
        ],
        'missing' => [
            'name' => 'Missing',
            'description' => 'Missing'
        ],
        'template' => [
            'name' => 'Template',
            'description' => 'Template'
        ],
        'unknown' => [
            'name' => 'Unknown',
            'description' => 'Unknown'
        ],
        'withheld' => [
            'name' => 'Withheld',
            'description' => 'Withheld'
        ]
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
     * Valid values for self::$remotelyHostedFunction.
     *
     * The array keys are the values to be set in self::remotelyHostedFunction.
     */
    const ONLINE_FUNCTION = [
        'download' => [
            'name' => 'Download',
            'description' => 'online instructions for transferring data from one storage device or system to another',
            'code' => '001',
        ],
        'information' => [
            'name' => 'Information',
            'description' => 'online information about the resource',
            'code' => '002',
        ],
        'offlineAccess' => [
            'name' => 'Offline Access',
            'description' => 'online instructions for requesting the resource from the provider',
            'code' => '003',
        ],
        'order' => [
            'name' => 'Order',
            'description' => 'online order process for obtaining the resource',
            'code' => '004',
        ],
        'search' => [
            'name' => 'Search',
            'description' => 'online search interface for seeking out information about the resource',
            'code' => '005',
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
     * @ORM\OneToOne(targetEntity="DatasetSubmissionReview", cascade={"persist"}, orphanRemoval=true)
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
     * @Serializer\Groups({"authors"})
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
     */
    protected $datasetFileUri;

    /**
     * The uri to the large file location.
     *
     * This specifies the location of the source of the large file url on a server.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     */
    protected $largeFileUri;

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
     * @Serializer\Groups({"card"})
     *
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $datasetFileSize;

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
     * The dataset file cold storage archive size.
     *
     * @var integer
     *
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $datasetFileColdStorageArchiveSize;

    /**
     * The dataset file cold storage archive sha256 hash.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $datasetFileColdStorageArchiveSha256Hash;

    /**
     * The dataset file cold storage archive original filename.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $datasetFileColdStorageOriginalFilename;

    /**
     * The date the file link was last checked.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    protected $datasetFileUrlLastCheckedDate;

    /**
     * The status code returned when the file link was checked.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $datasetFileUrlStatusCode;

    /**
     * Status of the dataset.
     *
     * Legacy DB column: metadata_status
     *
     * @var string
     *
     * @see DATASET_STATUSES class constant for valid values.
     *
     * @ORM\Column(type="text", nullable=false)
     */
    protected $datasetStatus = Dataset::DATASET_STATUS_NONE;

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
     * @Serializer\Groups({"card"})
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
     * DatasetSubmission's Distribution Points.
     *
     * @var Collection $distributionPoints
     *
     * @access protected
     *
     * @ORM\OneToMany(targetEntity="DistributionPoint", mappedBy="datasetSubmission", cascade={"persist"}, orphanRemoval=true)
     */
    protected $distributionPoints;

    /**
     * Remotely Hosted Dataset Name.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $remotelyHostedName;

    /**
     * Remotely Hosted Dataset Description.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $remotelyHostedDescription;

    /**
     * Remotely Hosted Dataset Function.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $remotelyHostedFunction;

    /**
     * Remotely Hosted Dataset URL.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $remotelyHostedUrl;

    /**
     * Fileset for the datasetSubmission instance.
     *
     * @var Fileset
     *
     * @ORM\OneToOne(targetEntity="Fileset", inversedBy="datasetSubmission", cascade={"persist", "remove"})
     */
    protected $fileset;

    /**
     * The Point of Contact for this Dataset Submission.
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\DatasetLink", mappedBy="datasetSubmission", cascade={"persist"}, orphanRemoval=true)
     */
    private $datasetLinks;

    /**
     * For cold-stored, the total unpacked filecount (not dirs).
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $coldStorageTotalUnpackedCount;

    /**
     * For cold-stored, the total unpacked bytecount.
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $coldStorageTotalUnpackedSize;

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
        $this->datasetContacts = new ArrayCollection();
        $this->metadataContacts = new ArrayCollection();
        $this->distributionPoints = new ArrayCollection();
        $this->datasetLinks = new ArrayCollection();
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

            $this->addDistributionPoint(new DistributionPoint());
        } elseif ($entity instanceof DatasetSubmission) {
            // Increment the sequence.
            $this->setSequence($entity->getDataset()->getDatasetSubmissionHistory()->first()->getSequence() + 1);
            // Populate from original Dataset Submission.
            $this->setDataset($entity->getDataset());
            $this->setTitle($entity->getTitle());
            $this->setShortTitle($entity->getShortTitle());
            $this->setAbstract($entity->getAbstract());
            $this->setAuthors($entity->getAuthors());
            $this->setRestrictions($entity->getRestrictions());
            $this->setDatasetFileTransferType($entity->getDatasetFileTransferType());
            $this->setDatasetFileUri($entity->getDatasetFileUri());
            $this->setLargeFileUri($entity->getLargeFileUri());
            $this->setDatasetFileTransferStatus($entity->getDatasetFileTransferStatus());
            $this->setDatasetStatus($entity->getDatasetStatus());
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
            $this->setRemotelyHostedName($entity->getRemotelyHostedName());
            $this->setRemotelyHostedDescription($entity->getRemotelyHostedDescription());
            $this->setRemotelyHostedFunction($entity->getRemotelyHostedFunction());
            $this->setRemotelyHostedUrl($entity->getRemotelyHostedUrl());
            $this->setDatasetFileUrlLastCheckedDate($entity->getDatasetFileUrlLastCheckedDate());
            $this->setDatasetFileUrlStatusCode($entity->getDatasetFileUrlStatusCode());
            $this->setDatasetFileColdStorageArchiveSha256Hash($entity->getDatasetFileColdStorageArchiveSha256Hash());
            $this->setDatasetFileColdStorageArchiveSize($entity->getDatasetFileColdStorageArchiveSize());
            $this->setDatasetFileColdStorageOriginalFilename($entity->getDatasetFileColdStorageOriginalFilename());
            $this->setColdStorageTotalUnpackedCount($entity->getColdStorageTotalUnpackedCount());
            $this->setColdStorageTotalUnpackedSize($entity->getColdStorageTotalUnpackedSize());

            //Submitter should always be the user who has submitted the dataset.
            if (!in_array($entity->getDatasetStatus(), [ Dataset::DATASET_STATUS_NONE, Dataset::DATASET_STATUS_BACK_TO_SUBMITTER])) {
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

            // Copy the original Dataset Submission's distribution Point(s).
            foreach ($entity->getDistributionPoints() as $distributionPoint) {
                $newDistributionPoint = new DistributionPoint();
                $newDistributionPoint->setDistributionUrl($distributionPoint->getDistributionUrl());
                $newDistributionPoint->setRoleCode($distributionPoint->getRoleCode());
                $newDistributionPoint->setDataCenter($distributionPoint->getDataCenter());
                $this->addDistributionPoint($newDistributionPoint);
            }

            // Copy the original Dataset Submission's Dataset Links.
            foreach ($entity->getDatasetLinks() as $datasetLink) {
                $newDatasetLink = new DatasetLink();
                $newDatasetLink->setUrl($datasetLink->getUrl());
                $newDatasetLink->setName($datasetLink->getName());
                $newDatasetLink->setDescription($datasetLink->getDescription());
                $newDatasetLink->setFunctionCode($datasetLink->getfunctionCode());
                $newDatasetLink->setProtocol($datasetLink->getProtocol());

                $this->addDatasetLink($newDatasetLink);
            }
            $fileset = $entity->getFileset();
            if ($fileset instanceof Fileset) {
                // Copy the fileSet
                $newFileset = new Fileset();
                foreach ($fileset->getAllFiles() as $file) {
                    $newFile = new File();
                    $newFile->setFilePathName($file->getFilePathName());
                    $newFile->setFileSize($file->getFileSize());
                    $newFile->setFileSha256Hash($file->getFileSha256Hash());
                    $newFile->setUploadedAt($file->getUploadedAt());
                    $newFile->setUploadedBy($file->getUploadedBy());
                    $newFile->setDescription($file->getDescription());
                    $newFile->setPhysicalFilePath($file->getPhysicalFilePath());
                    $newFile->setStatus($file->getStatus());
                    $newFileset->addFile($newFile);
                }
                if ($fileset->doesZipFileExist()) {
                    $newFileset->setZipFilePath($fileset->getZipFilePath());
                    $newFileset->setZipFileSha256Hash($fileset->getZipFileSha256Hash());
                    $newFileset->setZipFileSize($fileset->getZipFileSize());
                }
                $this->setFileset($newFileset);
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
            if (null === $this->temporalExtentNilReasonType) {
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

    /**
     * Get the choice list for NilReason types.
     *
     * @return array
     */
    public static function getNilReasonTypes()
    {
        return array_flip(
            array_map(
                function ($type) {
                    return $type['name'];
                },
                static::NILREASON_TYPES
            )
        );
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
        $this->datasetStatus = Dataset::DATASET_STATUS_SUBMITTED;
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
    public function reviewEvent(Person $reviewer, string $eventName)
    {
        switch (true) {
            case ($eventName === self::DATASET_END_REVIEW):
                //Setting the status to in-review.
                $this->status = self::STATUS_IN_REVIEW;
                $this->setDatasetStatus(Dataset::DATASET_STATUS_IN_REVIEW);
                break;
            case ($eventName === self::DATASET_ACCEPT_REVIEW):
                //Setting the status to in-review.
                $this->status = self::STATUS_COMPLETE;
                $this->setDatasetStatus(Dataset::DATASET_STATUS_ACCEPTED);
                if (!($this->getDataset()->getAcceptedDate() instanceof \DateTime)) {
                    $this->getDataset()->setAcceptedDate(new \DateTime('now', new \DateTimeZone('UTC')));
                }
                $this->getDataset()->setDatasetSubmission($this);
                break;
            case ($eventName === self::DATASET_REQUEST_REVISIONS):
                $this->status = self::STATUS_COMPLETE;
                $this->setDatasetStatus(Dataset::DATASET_STATUS_BACK_TO_SUBMITTER);
                $this->status = self::STATUS_COMPLETE;
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
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Set the Dataset Submission Review for this Dataset Submission.
     *
     * @param DatasetSubmissionReview|null $datasetSubmissionReview The Review for this Dataset Submission.
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
     * @return DatasetSubmissionReview|null
     */
    public function getDatasetSubmissionReview(): ?DatasetSubmissionReview
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
    public function getDataset(): Dataset
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
    public function setSequence(int $sequence)
    {
        if ('integer' !== gettype($sequence)) {
            throw new \InvalidArgumentException('Sequence must be an integer');
        }
        $this->sequence = $sequence;
    }

    /**
     * Get the sequence for this Dataset Submission.
     *
     * @return integer
     */
    public function getSequence(): int
    {
        return $this->sequence;
    }

    /**
     * Set the title for this Dataset Submission.
     *
     * @param string|null $title The title for this Dataset Submission.
     *
     * @return void
     */
    public function setTitle(?string $title)
    {
        $this->title = $title;
    }

    /**
     * Get the title for this Dataset Submission.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the short title for this Dataset Submission.
     *
     * @param string|null $shortTitle The short title for this Dataset Submission.
     *
     * @return void
     */
    public function setShortTitle(?string $shortTitle)
    {
        $this->shortTitle = $shortTitle;
    }

    /**
     * Get the short title for this Dataset Submission.
     *
     * @return string|null
     */
    public function getShortTitle(): ?string
    {
        return $this->shortTitle;
    }

    /**
     * Set the abstract for this Dataset Submission.
     *
     * @param string|null $abstract The abstract for this Dataset Submission.
     *
     * @return void
     */
    public function setAbstract(?string $abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * Get the abstract for this Dataset Submission.
     *
     * @return string|null
     */
    public function getAbstract(): ?string
    {
        return $this->abstract;
    }

    /**
     * Set the author(s) for this Dataset Submission.
     *
     * @param string|null $authors The author(s) for this Dataset Submission.
     *
     * @return void
     */
    public function setAuthors(?string $authors)
    {
        $this->authors = $authors;
    }

    /**
     * Get the author(s) for this Dataset Submission.
     *
     * @return string|null
     */
    public function getAuthors(): ?string
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
     * @return string|null
     */
    public function getPointOfContactName(): ?string
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
     * @return string|null
     */
    public function getPointOfContactEmail(): ?string
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
     * @param string|null $restrictions Whether the dataset has any restrictions.
     *
     * @see RESTRICTIONS class constant for valid values.
     *
     * @return void
     */
    public function setRestrictions(?string $restrictions)
    {
        $this->restrictions = $restrictions;
        $this->updateAvailabilityStatus();
    }

    /**
     * Get whether the dataset has any restrictions.
     *
     * @return string|null
     */
    public function getRestrictions(): ?string
    {
        return $this->restrictions;
    }

    /**
     * Set the dataset file transfer type.
     *
     * @param string|null $datasetFileTransferType The dataset file transfer type.
     *
     * @see TRANSFER_TYPES class constant for valid values.
     *
     * @return void
     */
    public function setDatasetFileTransferType(?string $datasetFileTransferType)
    {
        $this->datasetFileTransferType = $datasetFileTransferType;
    }

    /**
     * Get the dataset file transfer type.
     *
     * @return string|null
     */
    public function getDatasetFileTransferType(): ?string
    {
        return $this->datasetFileTransferType;
    }

    /**
     * Set the dataset file URI.
     *
     * @param string|null $datasetFileUri The dataset file URI.
     *
     * @return void
     */
    public function setDatasetFileUri(?string $datasetFileUri)
    {
        $this->datasetFileUri = $datasetFileUri;
    }

    /**
     * Get the dataset file URI.
     *
     * @return string|null
     */
    public function getDatasetFileUri(): ?string
    {
        return $this->datasetFileUri;
    }

    /**
     * Set the large file URI.
     *
     * @param string|null $largeFileUri The dataset file URI.
     *
     * @return void
     */
    public function setLargeFileUri(?string $largeFileUri)
    {
        $this->largeFileUri = $largeFileUri;
    }

    /**
     * Get the large file URI.
     *
     * @return string|null
     */
    public function getLargeFileUri(): ?string
    {
        return $this->largeFileUri;
    }

    /**
     * Set the dataset file transfer status.
     *
     * @param string|null $datasetFileTransferStatus The dataset file transfer status.
     *
     * @see TRANSFER_STATUSES class constant for valid values.
     *
     * @return void
     */
    public function setDatasetFileTransferStatus(?string $datasetFileTransferStatus)
    {
        $this->datasetFileTransferStatus = $datasetFileTransferStatus;
        $this->updateAvailabilityStatus();
    }

    /**
     * Get the dataset file transfer status.
     *
     * @return string|null
     */
    public function getDatasetFileTransferStatus(): ?string
    {
        return $this->datasetFileTransferStatus;
    }

    /**
     * Set the dataset file name.
     *
     * @param string|null $datasetFileName The dataset file name.
     *
     * @return void
     */
    public function setDatasetFileName(?string $datasetFileName)
    {
        $this->datasetFileName = $datasetFileName;
    }

    /**
     * Get the dataset file name.
     *
     * @return string|null
     */
    public function getDatasetFileName(): ?string
    {
        if ($this->getFileset() instanceof Fileset) {
            if ($this->getFileset()->doesZipFileExist()) {
                return basename($this->getFileset()->getZipFilePath());
            } elseif ($this->getFileset()->getProcessedAndNewFiles()->first() instanceof File) {
                return basename($this->getFileset()->getProcessedAndNewFiles()->first()->getFilePathName());
            }
        }
        return null;
    }

    /**
     * Set the dataset file size.
     *
     * @param integer|null $datasetFileSize The dataset file size.
     *
     * @return void
     */
    public function setDatasetFileSize(?int $datasetFileSize)
    {
        $this->datasetFileSize = $datasetFileSize;
    }

    /**
     * Get the dataset file size.
     *
     * @return integer|null
     */
    public function getDatasetFileSize(): ?int
    {
        if ($this->getFileset() instanceof Fileset) {
            return $this->getFileset()->getZipFileSize() ?? $this->getFileset()->getFileSize();
        }
        return null;
    }

    /**
     * Set the dataset file sha256 hash.
     *
     * @param string|null $datasetFileSha256Hash The dataset file sha256 hash.
     *
     * @return void
     */
    public function setDatasetFileSha256Hash(?string $datasetFileSha256Hash)
    {
        $this->datasetFileSha256Hash = $datasetFileSha256Hash;
    }

    /**
     * Get the dataset file sha256 hash.
     *
     * @return string|null
     */
    public function getDatasetFileSha256Hash(): ?string
    {
        if ($this->getFileset() instanceof Fileset) {
            if ($this->getFileset()->doesZipFileExist()) {
                return $this->getFileset()->getZipFileSha256Hash();
            } elseif ($this->getFileset()->getProcessedAndNewFiles()->first() instanceof File) {
                return $this->getFileset()->getProcessedAndNewFiles()->first()->getFileSha256Hash();
            }
        }
        return null;
    }

    /**
     * Set the hash of the single archive file to be stored in cold storage.
     *
     * @param string|null $datasetFileColdStorageArchiveSha256Hash Hash of the archive to be put into cold storage.
     *
     * @return void
     */
    protected function setDatasetFileColdStorageArchiveSha256Hash(?string $datasetFileColdStorageArchiveSha256Hash)
    {
        $this->datasetFileColdStorageArchiveSha256Hash = $datasetFileColdStorageArchiveSha256Hash;
    }

    /**
     * Get the hash of the single archive file to be stored in cold storage.
     *
     * @return string|null
     */
    public function getDatasetFileColdStorageArchiveSha256Hash(): ?string
    {
        return $this->datasetFileColdStorageArchiveSha256Hash;
    }

    /**
     * Set the size of the single archive file to be stored in cold storage.
     *
     * @param integer|null $datasetFileColdStorageArchiveSize The archive size, in bytes.
     *
     * @return void
     */
    protected function setDatasetFileColdStorageArchiveSize(?int $datasetFileColdStorageArchiveSize)
    {
        $this->datasetFileColdStorageArchiveSize = $datasetFileColdStorageArchiveSize;
    }

    /**
     * Get the size of the single archive file to be stored in cold storage, in bytes.
     *
     * @return integer|null
     */
    public function getDatasetFileColdStorageArchiveSize(): ?int
    {
        return $this->datasetFileColdStorageArchiveSize;
    }

    /**
     * Set the original filename of the archive file to be stored in cold storage.
     *
     * @param string|null $datasetFileColdStorageOriginalFilename The original filename to be preserved.
     *
     * @return void
     */
    protected function setDatasetFileColdStorageOriginalFilename(?string $datasetFileColdStorageOriginalFilename)
    {
        $this->datasetFileColdStorageOriginalFilename = $datasetFileColdStorageOriginalFilename;
    }

    /**
     * Get the original filename of the archive file stored in cold storage.
     *
     * @return string|null
     */
    public function getDatasetFileColdStorageOriginalFilename(): ?string
    {
        return $this->datasetFileColdStorageOriginalFilename;
    }

    /**
     * Set the cold storage attributes, as one action, no nulls allowed.
     *
     * @param integer      $filesize   The original file size, in bytes, to be preserved.
     * @param string       $hash       The original file sha256 hash to be preserved.
     * @param string       $filename   The original file name to be preserved.
     * @param integer|null $totalCount The total count of files as unpacked.
     * @param integer|null $totalSize  The total count of files as unpacked.
     *
     * @return void
     */
    public function setDatasetFileColdStorageAttributes(int $filesize, string $hash, string $filename, ?int $totalCount, ?int $totalSize)
    {
        $this->setDatasetFileColdStorageOriginalFilename($filename);
        $this->setDatasetFileColdStorageArchiveSha256Hash($hash);
        $this->setDatasetFileColdStorageArchiveSize($filesize);
        $this->setColdStorageTotalUnpackedCount($totalCount);
        $this->setColdStorageTotalUnpackedSize($totalSize);
    }

    /**
     * Clear all cold storage attributes.
     *
     * @return void
     */
    public function clearDatasetFileColdStorageAttributes()
    {
        $this->setDatasetFileColdStorageOriginalFilename(null);
        $this->setDatasetFileColdStorageArchiveSha256Hash(null);
        $this->setDatasetFileColdStorageArchiveSize(null);
        $this->setColdStorageTotalUnpackedCount(null);
        $this->setColdStorageTotalUnpackedSize(null);
    }

    /**
     * Check if the file is stored in cold storage based on the values of Sha256Hash and FileSize.
     *
     * @Serializer\Groups({"coldStorage"})
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("coldStorage")
     *
     * @return boolean
     */
    public function isDatasetFileInColdStorage()
    {
        if (
            null !== $this->datasetFileColdStorageArchiveSize &&
            null !== $this->datasetFileColdStorageArchiveSha256Hash &&
            null !== $this->datasetFileColdStorageOriginalFilename
        ) {
            return true;
        }
        return false;
    }

    /**
     * Set the date when the dataset file link was last checked.
     *
     * @param \DateTime $datasetFileUrlLastCheckedDate The last check date.
     *
     * @return void
     */
    public function setDatasetFileUrlLastCheckedDate(\DateTime $datasetFileUrlLastCheckedDate = null)
    {
        $this->datasetFileUrlLastCheckedDate = $datasetFileUrlLastCheckedDate;
    }

    /**
     * Get the date when the dataset file link was last checked.
     *
     * @return \DateTime
     */
    public function getDatasetFileUrlLastCheckedDate(): ?\DateTime
    {
        return $this->datasetFileUrlLastCheckedDate;
    }

    /**
     * Set the dataset file url status code.
     *
     * @param string|null $datasetFileUrlStatusCode The dataset dataset file url status code.
     *
     * @return void
     */
    public function setDatasetFileUrlStatusCode(?string $datasetFileUrlStatusCode)
    {
        $this->datasetFileUrlStatusCode = $datasetFileUrlStatusCode;
    }

    /**
     * Get the dataset file url status code.
     *
     * @return string|null
     */
    public function getDatasetFileUrlStatusCode(): ?string
    {
        return $this->datasetFileUrlStatusCode;
    }

    /**
     * Set the status of the metadata.
     *
     * @param string|null $datasetStatus The status of the metadata.
     *
     * @see DATASET_STATUSES class constant for valid values.
     *
     * @return void
     */
    public function setDatasetStatus(?string $datasetStatus)
    {
        $this->datasetStatus = $datasetStatus;
        $this->updateDatasetStatus();
        $this->updateAvailabilityStatus();
    }

    /**
     * Get the status of the metadata.
     *
     * @return string|null
     */
    public function getDatasetStatus(): ?string
    {
        return $this->datasetStatus;
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
    public function getDatasetSubmissionId(): ?string
    {
        // If the dataset is not set or the dataset does not have an UDI.
        if (!$this->dataset instanceof Dataset or null === $this->dataset->getUdi()) {
            return null;
        }
        return $this->dataset->getUdi() . '.' . sprintf('%03d', $this->sequence);
    }

    /**
     * Sets the purpose.
     *
     * @param string|null $purpose The purpose of the dataset.
     *
     * @return void
     */
    public function setPurpose(?string $purpose)
    {
        $this->purpose = $purpose;
    }

    /**
     * Gets the purpose.
     *
     * @return string|null
     */
    public function getPurpose(): ?string
    {
        return $this->purpose;
    }

    /**
     * Sets the Supplemental Information - Data Parameters and Units.
     *
     * @param string|null $suppParams Supplemental data parameters and units.
     *
     * @return void
     */
    public function setSuppParams(?string $suppParams)
    {
        $this->suppParams = $suppParams;
    }

    /**
     * Gets the Supplemental Information - Data Parameters and Units.
     *
     * @return string|null
     */
    public function getSuppParams(): ?string
    {
        return $this->suppParams;
    }

    /**
     * Sets the Supplemental Information - Methods.
     *
     * @param string|null $suppMethods Supplemental data methods.
     *
     * @return void
     */
    public function setSuppMethods(?string $suppMethods)
    {
        $this->suppMethods = $suppMethods;
    }

    /**
     * Gets the Supplemental Information - Methods.
     *
     * @return string|null
     */
    public function getSuppMethods(): ?string
    {
        return $this->suppMethods;
    }

    /**
     * Sets the Supplemental Information - Instruments.
     *
     * @param string|null $suppInstruments Supplemental data - instruments.
     *
     * @return void
     */
    public function setSuppInstruments(?string $suppInstruments)
    {
        $this->suppInstruments = $suppInstruments;
    }

    /**
     * Gets the Supplemental Information - Instruments.
     *
     * @return string|null
     */
    public function getSuppInstruments(): ?string
    {
        return $this->suppInstruments;
    }

    /**
     * Sets the Supplemental Information - sampling scales and rates.
     *
     * @param string|null $suppSampScalesRates Supplemental data - sampling scales and rates.
     *
     * @return void
     */
    public function setSuppSampScalesRates(?string $suppSampScalesRates)
    {
        $this->suppSampScalesRates = $suppSampScalesRates;
    }

    /**
     * Gets the Supplemental Information - sampling scales and rates.
     *
     * @return string|null
     */
    public function getSuppSampScalesRates(): ?string
    {
        return $this->suppSampScalesRates;
    }

    /**
     * Sets the Supplemental Information - error analysis.
     *
     * @param string|null $suppErrorAnalysis Supplemental data - error analysis.
     *
     * @return void
     */
    public function setSuppErrorAnalysis(?string $suppErrorAnalysis)
    {
        $this->suppErrorAnalysis = $suppErrorAnalysis;
    }

    /**
     * Gets the Supplemental Information - error analysis.
     *
     * @return string|null
     */
    public function getSuppErrorAnalysis(): ?string
    {
        return $this->suppErrorAnalysis;
    }

    /**
     * Sets the Supplemental Information - provenance and historical references.
     *
     * @param string|null $suppProvenance Supplemental data - provenance and historical references.
     *
     * @return void
     */
    public function setSuppProvenance(?string $suppProvenance)
    {
        $this->suppProvenance = $suppProvenance;
    }

    /**
     * Gets the Supplemental Information - provenance and historical references.
     *
     * @return string|null
     */
    public function getSuppProvenance(): ?string
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
    public function getThemeKeywords(): array
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
    public function getPlaceKeywords(): array
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
    public function getTopicKeywords(): array
    {
        return $this->topicKeywords;
    }

    /**
     * Setter for geographic spatial extent.
     *
     * @param string|null $spatialExtent Well-Known text of dataset's geometry.
     *
     * @return void
     */
    public function setSpatialExtent(?string $spatialExtent)
    {
        $this->spatialExtent = $spatialExtent;
    }

    /**
     * Getter for geographic spatial extent.
     *
     * @return string|null As WKT.
     */
    public function getSpatialExtent(): ?string
    {
        return $this->spatialExtent;
    }

    /**
     * Setter for spatial extent description.
     *
     * @param string|null $spatialExtentDescription Description of spatial extent.
     *
     * @return void
     */
    public function setSpatialExtentDescription(?string $spatialExtentDescription)
    {
        $this->spatialExtentDescription = $spatialExtentDescription;
    }

    /**
     * Getter for spatial extent description.
     *
     * @return string|null
     */
    public function getSpatialExtentDescription(): ?string
    {
        return $this->spatialExtentDescription;
    }

    /**
     * Setter for dataset's temporal extent description.
     *
     * @param string|null $temporalExtentDesc Description of temporal extent, either 'ground condition' or 'modeled period'.
     *
     * @throws \InvalidArgumentException If $temporalExtentDesc is not in static::TEMPORAL_EXTENT_DESCRIPTIONS.
     *
     * @return void
     */
    public function setTemporalExtentDesc(?string $temporalExtentDesc)
    {
        if (
            null !== $temporalExtentDesc and
            !array_key_exists($temporalExtentDesc, static::TEMPORAL_EXTENT_DESCRIPTIONS)
        ) {
            throw new \InvalidArgumentException("'$temporalExtentDesc' is not a valid value for temporalExtentDesc");
        }
        $this->temporalExtentDesc = $temporalExtentDesc;
    }

    /**
     * Getter for dataset's temporal extent description.
     *
     * @return string|null
     */
    public function getTemporalExtentDesc(): ?string
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
        $this->temporalExtentBeginPosition = $temporalExtentBeginPosition;
    }

    /**
     * Get the dataset's temporal extent begin position.
     *
     * @return \DateTime
     */
    public function getTemporalExtentBeginPosition(): ?\DateTime
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
        $this->temporalExtentEndPosition = $temporalExtentEndPosition;
    }

    /**
     * Get the dataset's temporal extent end position.
     *
     * @return \DateTime
     */
    public function getTemporalExtentEndPosition(): ?\DateTime
    {
        return $this->temporalExtentEndPosition;
    }

    /**
     * Set the distribution format name.
     *
     * @param string|null $distributionFormatName The distribution format name.
     *
     * @return void
     */
    public function setDistributionFormatName(?string $distributionFormatName)
    {
        $this->distributionFormatName = $distributionFormatName;
    }

    /**
     * Get the distribution format name.
     *
     * @return string|null
     */
    public function getDistributionFormatName(): ?string
    {
        return $this->distributionFormatName;
    }

    /**
     * Set the file decompression technique.
     *
     * @param string|null $fileDecompressionTechnique The file decompression technique.
     *
     * @return void
     */
    public function setFileDecompressionTechnique(?string $fileDecompressionTechnique)
    {
        $this->fileDecompressionTechnique = $fileDecompressionTechnique;
    }

    /**
     * Get the file decompression technique.
     *
     * @return string|null
     */
    public function getFileDecompressionTechnique(): ?string
    {
        return $this->fileDecompressionTechnique;
    }

    /**
     * Get the submission time stamp.
     *
     * @return \DateTime
     */
    public function getSubmissionTimeStamp(): ?\DateTime
    {
        return $this->submissionTimeStamp;
    }

    /**
     * Get the submitter.
     *
     * @return Person|null
     */
    public function getSubmitter(): ?Person
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
    protected function updateDatasetStatus()
    {
        if ($this->getDataset() instanceof Dataset) {
            $this->getDataset()->setDatasetStatus($this->getDatasetStatus());
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
     * Gets the valid choices for topic keywords.
     *
     * @return array
     */
    public static function getTopicKeywordsChoices(): array
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
    public static function getTemporalExtentDescChoices(): array
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
     * Gets the valid choices for Online Function codes.
     *
     * @return array
     */
    public static function getOnlineFunctionCodes(): array
    {
        return array_flip(
            array_map(
                function ($type) {
                    return $type['name'];
                },
                static::ONLINE_FUNCTION
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
     * @return string|null
     */
    public function getTemporalExtentNilReasonType(): ?string
    {
        return $this->temporalExtentNilReasonType;
    }

    /**
     * Sets the temporal nilreason type for the dataset.
     *
     * @param string|null $temporalExtentNilReasonType The nilReason for temporal extent.
     *
     * @throws \InvalidArgumentException If $temporalExtentNilReasonType is not in self::NILREASON_TYPES.
     *
     * @return void
     */
    public function setTemporalExtentNilReasonType(?string $temporalExtentNilReasonType)
    {
        if (null !== $temporalExtentNilReasonType and !array_key_exists($temporalExtentNilReasonType, self::NILREASON_TYPES)) {
                throw new \InvalidArgumentException("'$temporalExtentNilReasonType' is not a valid value for nilReason types");
        }
        $this->temporalExtentNilReasonType = $temporalExtentNilReasonType;
    }

    /**
     * Adder for distributionPoint.
     *
     * @param DistributionPoint $distributionPoint Single object to be added.
     *
     * @access public
     *
     * @return void
     */
    public function addDistributionPoint(DistributionPoint $distributionPoint)
    {
        $distributionPoint->setDatasetSubmission($this);
        $this->distributionPoints->add($distributionPoint);
    }

    /**
     * Remover for Distribution Point.
     *
     * @param DistributionPoint $distributionPoint Single object to be removed.
     *
     * @access public
     *
     * @return void
     */
    public function removeDistributionPoint(DistributionPoint $distributionPoint)
    {
        $this->distributionPoints->removeElement($distributionPoint);
    }

    /**
     * Getter for distributionPoints.
     *
     * @access public
     *
     * @return Collection Distribution Point associations for this Dataset Submission.
     */
    public function getDistributionPoints()
    {
        return $this->distributionPoints;
    }

     /**
     * Getter for the (first) erddap url link.
     *
     * @return DatasetLink|null
     */
    public function getErdappDatasetLink(): ?DatasetLink
    {
        $datasetLinks = $this->getDatasetLinks()->filter(function (DatasetLink $datasetLink) {
            return $datasetLink->getName() === DatasetLink::LINK_NAME_CODES["erddap"]["name"];
        });

        if ($datasetLinks->count() > 0) {
            return $datasetLinks->first();
        }

        return null;
    }

    /**
     * Getter for the NCEI url.
     *
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"card"})
     *
     * @return string|null
     */
    public function getNceiUrl(): ?string
    {
        $datasetLinks = $this->getDatasetLinks()->filter(function (DatasetLink $datasetLink) {
            return $datasetLink->getName() === DatasetLink::LINK_NAME_CODES["ncei"]["name"];
        });

        if ($datasetLinks->count() > 0) {
            $nceiLink = $datasetLinks->first();
            if ($nceiLink instanceof DatasetLink) {
                return $nceiLink->getUrl();
            }
        }
        return null;
    }

    /**
     * Getter for the erddap url.
     *
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"card"})
     *
     * @return string|null
     */
    public function getErddapUrl(): ?string
    {
        $erddapLink = $this->getErdappDatasetLink();

        if ($erddapLink instanceof DatasetLink) {
            return $erddapLink->getUrl();
        }

        return null;
    }

    /**
     * Getter for the erddap url protocol.
     *
     * @return string|null
     */
    public function getErddapUrlProtocol(): ?string
    {
        $erddapLink = $this->getErdappDatasetLink();

        if ($erddapLink instanceof DatasetLink) {
            return $erddapLink->getProtocol();
        }

        return null;
    }

    /**
     * Getter for the datasetFileUri url protocol.
     *
     * @return string|null
     */
    public function getDatasetFileUriProtocol(): ?string
    {
        if ($this->datasetFileUri !== null) {
            preg_match('/^(.*?):.*$/', $this->datasetFileUri, $matches);
            return $matches[1];
        } else {
            return null;
        }
    }

    /**
     * Getter for the Remotely Hosted Name.
     *
     * @return string|null
     */
    public function getRemotelyHostedName(): ?string
    {
        return $this->remotelyHostedName;
    }

    /**
     * Setter for the Remotely Hosted Name.
     *
     * @param string|null $remotelyHostedName Erddap url.
     *
     * @return void
     */
    public function setRemotelyHostedName(?string $remotelyHostedName)
    {
        $this->remotelyHostedName = $remotelyHostedName;
    }

    /**
     * Getter for the Remotely Hosted Description.
     *
     * @return string|null
     */
    public function getRemotelyHostedDescription(): ?string
    {
        return $this->remotelyHostedDescription;
    }

    /**
     * Setter for the Remotely Hosted Description.
     *
     * @param string|null $remotelyHostedDescription Erddap url.
     *
     * @return void
     */
    public function setRemotelyHostedDescription(?string $remotelyHostedDescription)
    {
        $this->remotelyHostedDescription = $remotelyHostedDescription;
    }

    /**
     * Getter for the Remotely Hosted Function.
     *
     * @return string|null
     */
    public function getRemotelyHostedFunction(): ?string
    {
        return $this->remotelyHostedFunction;
    }

    /**
     * Setter for the Remotely Hosted Function.
     *
     * @param string|null $remotelyHostedFunction Remotely Hosted Function.
     *
     * @see ONLINE_FUNCTION class constant for valid values.
     *
     * @return void
     */
    public function setRemotelyHostedFunction(?string $remotelyHostedFunction)
    {
        $this->remotelyHostedFunction = $remotelyHostedFunction;
    }

    /**
     * Getter for fileset entity.
     *
     * @return Fileset|null
     */
    public function getFileset(): ?Fileset
    {
        return $this->fileset;
    }

    /**
     * Setter for fileset entity.
     *
     * @param Fileset $fileset The fileset entity associated with this datasetSubmission instance.
     *
     * @return void
     */
    public function setFileset(Fileset $fileset): void
    {
        $this->fileset = $fileset;
    }

    /**
     * Getter for the Dataset Links.
     *
     * @return Collection|DatasetLink[]
     */
    public function getDatasetLinks(): Collection
    {
        return $this->datasetLinks;
    }

    /**
     * Adder for dataset link.
     *
     * @param DatasetLink $datasetLink A dataset link.
     *
     * @return self
     */
    public function addDatasetLink(DatasetLink $datasetLink): self
    {
        if (!$this->datasetLinks->contains($datasetLink)) {
            $this->datasetLinks[] = $datasetLink;
            $datasetLink->setDatasetSubmission($this);
        }

        return $this;
    }

    /**
     * Remover for dataset link.
     *
     * @param DatasetLink $datasetLink A dataset link.
     *
     * @return self
     */
    public function removeDatasetLink(DatasetLink $datasetLink): self
    {
        if ($this->datasetLinks->contains($datasetLink)) {
            $this->datasetLinks->removeElement($datasetLink);
            // set the owning side to null (unless already changed)
            if ($datasetLink->getDatasetSubmission() === $this) {
                $datasetLink->setDatasetSubmission(null);
            }
        }

        return $this;
    }

    /**
     * Getter for remotely hosted url.
     *
     * @return string|null
     */
    public function getRemotelyHostedUrl(): ?string
    {
        return $this->remotelyHostedUrl;
    }

    /**
     * Setter for remotely hosted url.
     *
     * @param string|null $remotelyHostedUrl Remotely hosted url string.
     *
     * @return void
     */
    public function setRemotelyHostedUrl(?string $remotelyHostedUrl): void
    {
        $this->remotelyHostedUrl = $remotelyHostedUrl;
    }

    /**
     * Check if dataset submission is marked as remotely hosted.
     *
     * @return bool
     */
    public function isRemotelyHosted(): bool
    {
        $isMarked = false;
        if (
            $this->remotelyHostedUrl and $this->remotelyHostedName and
            $this->remotelyHostedFunction and $this->remotelyHostedDescription
        ) {
            $isMarked = true;
        }
        return $isMarked;
    }

    /**
     * Getter for total cold-stored unpacked size, in bytes.
     *
     * @return integer|null
     */
    public function getColdStorageTotalUnpackedSize(): ?int
    {
        return $this->coldStorageTotalUnpackedSize;
    }

    /**
     * Setter for total cold-stored unpacked size, in bytes.
     *
     * @param integer|null $coldStorageTotalUnpackedSize Aggregate bytecount of all cold-stored files in a dataset, as unpacked.
     *
     * @return DatasetSubmission
     */
    public function setColdStorageTotalUnpackedSize(?int $coldStorageTotalUnpackedSize): self
    {
        $this->coldStorageTotalUnpackedSize = $coldStorageTotalUnpackedSize;
        return $this;
    }

    /**
     * Getter for total cold-storded unpacked file count.
     *
     * @return integer|null
     */
    public function getColdStorageTotalUnpackedCount(): ?int
    {
        return $this->coldStorageTotalUnpackedCount;
    }

    /**
     * Setter for total cold-storded unpacked file count.
     *
     * @param integer|null $coldStorageTotalUnpackedSize Aggregate filecount of all cold-stored files in a dataset, as unpacked.
     *
     * @return DatasetSubmission
     */
    public function setColdStorageTotalUnpackedCount(?int $coldStorageTotalUnpackedCount): self
    {
        $this->coldStorageTotalUnpackedCount = $coldStorageTotalUnpackedCount;
        return $this;
    }

    /**
     * Sets the issue tracking ticket for this Dataset.
     *
     * @param string|null $issueTrackingTicket The identifier for an issue tracking ticket related to this Dataset.
     *
     * @return DatasetSubmission
     */
    public function setIssueTrackingTicket(?string $issueTrackingTicket): self
    {
        $dataset = $this->getDataset();
        if ($dataset instanceof Dataset) {
            $dataset->setIssueTrackingTicket($issueTrackingTicket);
        }

        return $this;
    }

    /**
     * Gets the issue tracking ticket for this Dataset.
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("issueTrackingTicket")
     *
     * @return string
     */
    public function getIssueTrackingTicket(): string
    {
        $dataset = $this->getDataset();
        if ($dataset instanceof Dataset) {
            return $dataset->getIssueTrackingTicket() ?? '';
        }

        return '';
    }
}
