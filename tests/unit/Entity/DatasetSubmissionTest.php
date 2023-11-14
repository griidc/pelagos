<?php

namespace App\Tests\Entity;

use App\Entity\DataCenter;
use App\Entity\Dataset;
use App\Entity\DatasetLink;
use App\Entity\DatasetSubmission;
use App\Entity\DatasetSubmissionReview;
use App\Entity\DIF;
use App\Entity\DistributionPoint;
use App\Entity\File;
use App\Entity\Fileset;
use App\Entity\Person;
use App\Entity\PersonDatasetSubmissionDatasetContact;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\DatasetSubmission.
 */
class DatasetSubmissionTest extends TestCase
{
    /**
     * Dataset Submission undergoing test.
     *
     * @var DatasetSubmission
     */
    protected $datasetSubmission;

    /**
     * A mock Dataset.
     *
     * @var Dataset
     */
    protected $mockDataset;

    /**
     * A mock DIF.
     *
     * @var DIF
     */
    protected $mockDif;

    /**
     * A mock PersonDatasetSubmissionDatasetContact.
     *
     * @var PersonDatasetSubmissionDatasetContact
     */
    protected $mockPersonDatasetSubmissionDatasetContact;

    /**
     * A mock Person.
     *
     * @var Person
     */
    protected $mockPerson;

    /**
     * A mock Dataset Submission Review.
     *
     * @var DatasetSubmissionReview
     */
    protected $mockDatasetSubmissionReview;

    /**
     * Mock object for Fileset entity instance.
     *
     * @var Fileset
     */
    protected $mockFileset;

    /**
     * Mock object for File entity instance.
     *
     * @var File
     */
    protected $mockFile;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of DatasetSubmission and sets its properties.
     *
     * @return void
     */
    public function setUp()
    {
        $this->mockDataset = \Mockery::mock(
            Dataset::class,
            [
                'setDatasetSubmission' => null,
                'setDatasetSubmissionStatus' => null,
                'setDatasetStatus' => null,
                'setAvailabilityStatus' => null,
                'updateAvailabilityStatus' => null,
                'getUdi' => 'T1.x123.000:0001',
                'setAcceptedDate' => null,
            ]
        );
        $this->mockPerson = \Mockery::mock(
            Person::class,
            [
                'getLastName' => 'Person',
                'getFirstName' => 'Mock',
                'getEmailAddress' => 'mock.person@test.null',
            ]
        );
        $this->mockDif = \Mockery::mock(
            DIF::class,
            [
                'getDataset' => $this->mockDataset,
                'getTitle' => 'Title from DIF',
                'getAbstract' => 'Abstract from DIF',
                'getVariablesObserved' => null,
                'getSpatialExtentGeometry' => null,
                'getSpatialExtentDescription' => null,
                'getPrimaryPointOfContact' => $this->mockPerson,
                'getSecondaryPointOfContact' => null,
                'getAdditionalFunders' => null,
                'getKeywords' => new ArrayCollection(),
            ]
        );
        $this->mockPersonDatasetSubmissionDatasetContact = \Mockery::mock(
            PersonDatasetSubmissionDatasetContact::class,
            [
                'setDatasetSubmission' => null,
                'getPerson' => $this->mockPerson,
                'setPerson' => null,
                'setRole' => null,
                'setPrimaryContact' => null,
            ]
        );
        $this->mockFile = \Mockery::mock(
            File::class,
            [
                'setFileset' => \Mockery::mock(Fileset::class),
                'setFilePathName' => 'foobar.baz',
                'getFilePathName' => 'foobar.baz',
                'setFileSize' => 1234,
                'getFileSize' => 1234,
                'setFileSha256Hash' => 'cafe',
                'getFileSha256Hash' => 'cafe',
            ]
        );

        $this->mockFileset = \Mockery::mock(
            Fileset::class,
            [
                'getAllFiles' => new ArrayCollection([$this->mockFile]),
                'getFileSize' => 1234,
                'getZipFilePath' => '/path/to/zip',
                'getZipFileSha256Hash' => 'cfsdaf',
                'getZipFileSize' => 32432324,
                'doesZipFileExist' => true,
                'getProcessedAndNewFiles' => new ArrayCollection(),
                'setZipFileSize' => 0,
            ]
        );
        $this->datasetSubmission = new DatasetSubmission(
            $this->mockDif,
            $this->mockPersonDatasetSubmissionDatasetContact
        );

        $this->datasetSubmission = new DatasetSubmission(
            $this->mockDif,
            $this->mockPersonDatasetSubmissionDatasetContact
        );

        $this->datasetSubmission->setFileset($this->mockFileset);
    }

    /**
     * Test status.
     *
     * @return void
     */
    public function testStatus()
    {
        $this->datasetSubmission->setDataset($this->mockDataset);
        $this->assertEquals(
            DatasetSubmission::STATUS_INCOMPLETE,
            $this->datasetSubmission->getStatus()
        );
        $this->datasetSubmission->submit($this->mockPerson);
        $this->assertEquals(
            DatasetSubmission::STATUS_COMPLETE,
            $this->datasetSubmission->getStatus()
        );
    }

    /**
     * Test dataset setter and getter.
     *
     * @return void
     */
    public function testDataset()
    {
        $this->datasetSubmission->setDataset($this->mockDataset);
        $this->assertSame(
            $this->mockDataset,
            $this->datasetSubmission->getDataset()
        );
    }

    /**
     * Test sequence setter and getter.
     *
     * @return void
     */
    public function testSequence()
    {
        $this->datasetSubmission->setSequence(42);
        $this->assertEquals(
            42,
            $this->datasetSubmission->getSequence()
        );
    }

    /**
     * Test basic info setters and getters: title, shortTitle, abstract, authors, primary POC.
     *
     * @return void
     */
    public function testBasicInfo()
    {
        $this->datasetSubmission->setTitle(null);
        $this->datasetSubmission->setTitle('test title');
        $this->assertEquals(
            'test title',
            $this->datasetSubmission->getTitle()
        );
        $this->datasetSubmission->setShortTitle(null);
        $this->datasetSubmission->setShortTitle('test short title');
        $this->assertEquals(
            'test short title',
            $this->datasetSubmission->getShortTitle()
        );
        $this->datasetSubmission->setAbstract(null);
        $this->datasetSubmission->setAbstract('test abstract');
        $this->assertEquals(
            'test abstract',
            $this->datasetSubmission->getAbstract()
        );
        $this->datasetSubmission->setAuthors(null);
        $this->datasetSubmission->setAuthors('test authors');
        $this->assertEquals(
            'test authors',
            $this->datasetSubmission->getAuthors()
        );
    }

    /**
     * Test datasetContacts.
     *
     * @return void
     */
    public function testDatasetContacts()
    {
        // datasetContacts should be a Collection.
        $this->assertInstanceOf(
            Collection::class,
            $this->datasetSubmission->getDatasetContacts()
        );
        // datasetContacts should be initially have a single contact, from DIF.
        $this->assertEquals(
            1,
            $this->datasetSubmission->getDatasetContacts()->count()
        );
        // The item should be the mock PersonDatasetSubmissionDatasetContact.
        $this->assertSame(
            $this->mockPersonDatasetSubmissionDatasetContact,
            $this->datasetSubmission->getDatasetContacts()->first()
        );
        // Test removeContact.
        $this->datasetSubmission->removeDatasetContact($this->mockPersonDatasetSubmissionDatasetContact);
        $this->assertEquals(
            0,
            $this->datasetSubmission->getDatasetContacts()->count()
        );
        // Test addContact method.
        $this->datasetSubmission->addDatasetContact($this->mockPersonDatasetSubmissionDatasetContact);
        $this->assertSame(
            $this->mockPersonDatasetSubmissionDatasetContact,
            $this->datasetSubmission->getDatasetContacts()->first()
        );
    }

    /**
     * Test point of contact name and email.
     *
     * @return void
     */
    public function testPointOfContact()
    {
        $this->assertEquals(
            'Person, Mock',
            $this->datasetSubmission->getPointOfContactName()
        );
        $this->assertEquals(
            'mock.person@test.null',
            $this->datasetSubmission->getPointOfContactEmail()
        );
    }

    /**
     * Test restrictions.
     *
     * @return void
     */
    public function testRestrictions()
    {
        $this->datasetSubmission->setRestrictions(DatasetSubmission::RESTRICTION_NONE);
        $this->assertEquals(
            DatasetSubmission::RESTRICTION_NONE,
            $this->datasetSubmission->getRestrictions()
        );
    }

    /**
     * Test dataset file attributes.
     *
     * @return void
     */
    public function testDatasetFileAttributes()
    {
        $this->datasetSubmission->setDatasetFileTransferType(DatasetSubmission::TRANSFER_TYPE_UPLOAD);
        $this->assertEquals(
            DatasetSubmission::TRANSFER_TYPE_UPLOAD,
            $this->datasetSubmission->getDatasetFileTransferType()
        );
        $this->datasetSubmission->setDatasetFileUri('foobar');
        $this->assertEquals(
            'foobar',
            $this->datasetSubmission->getDatasetFileUri()
        );
        $this->datasetSubmission->setLargeFileUri('largefoobar');
        $this->assertEquals(
            'largefoobar',
            $this->datasetSubmission->getLargeFileUri()
        );
        $this->datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
        $this->assertEquals(
            DatasetSubmission::TRANSFER_STATUS_COMPLETED,
            $this->datasetSubmission->getDatasetFileTransferStatus()
        );
    }

    /**
     * Test dataset cold-storage related file attributes.
     *
     * @return void
     */
    public function testCanSetAndGetDatasetFileColdStorageAttributes()
    {
        // values used for testing
        $testHash = '66a045b452102c59d840ec097d59d9467e13a3f34f6494e539ffd32c1bb35f18';
        $testSize = 42;
        $testName = 'test.dat';
        $testUnpackedFileCount = '1234';
        $testUnpackedFileSize = '5678';

        // This public setting internally calls protected individual setters in the entity, for coverage.
        $this->datasetSubmission->setDatasetFileColdStorageAttributes($testSize, $testHash, $testName, $testUnpackedFileCount, $testUnpackedFileSize);

        $this->assertEquals(
            $testHash,
            $this->datasetSubmission->getDatasetFileColdStorageArchiveSha256Hash()
        );
        $this->assertEquals(
            $testSize,
            $this->datasetSubmission->getDatasetFileColdStorageArchiveSize()
        );
        $this->assertEquals(
            $testName,
            $this->datasetSubmission->getDatasetFileColdStorageOriginalFilename()
        );
        $this->assertEquals(
            $testUnpackedFileCount,
            $this->datasetSubmission->getColdStorageTotalUnpackedCount()
        );
        $this->assertEquals(
            $testUnpackedFileSize,
            $this->datasetSubmission->getColdStorageTotalUnpackedSize()
        );

        $this->datasetSubmission->clearDatasetFileColdStorageAttributes();
        $this->assertNull($this->datasetSubmission->getDatasetFileColdStorageArchiveSha256Hash());
        $this->assertNull($this->datasetSubmission->getDatasetFileColdStorageArchiveSize());
        $this->assertNull($this->datasetSubmission->getDatasetFileColdStorageOriginalFilename());
        $this->assertNull($this->datasetSubmission->getColdStorageTotalUnpackedCount());
        $this->assertNull($this->datasetSubmission->getColdStorageTotalUnpackedSize());
    }

    /**
     * Test dataset status.
     *
     * @return void
     */
    public function testDatasetStatus()
    {
        $this->datasetSubmission->setDatasetStatus(Dataset::DATASET_STATUS_ACCEPTED);
        $this->assertEquals(
            Dataset::DATASET_STATUS_ACCEPTED,
            $this->datasetSubmission->getDatasetStatus()
        );
    }

    /**
     * Test getDatasetSubmissionId.
     *
     * @return void
     */
    public function testGetDatasetSubmissionId()
    {
        $this->datasetSubmission->setSequence(1);
        $this->assertEquals(
            'T1.x123.000:0001.001',
            $this->datasetSubmission->getDatasetSubmissionId()
        );
    }

    /**
     * Test purpose.
     *
     * @return void
     */
    public function testPurpose()
    {
        $this->datasetSubmission->setPurpose(null);
        $purpose = 'To test purpose';
        $this->datasetSubmission->setPurpose($purpose);
        $this->assertEquals(
            $purpose,
            $this->datasetSubmission->getPurpose()
        );
    }

    /**
     * Test supplementals.
     *
     * @return void
     */
    public function testSupplementals()
    {
        $this->datasetSubmission->setSuppParams(null);
        $suppParams = 'supplemental parameters';
        $this->datasetSubmission->setSuppParams($suppParams);
        $this->assertEquals(
            $suppParams,
            $this->datasetSubmission->getSuppParams()
        );
        $this->datasetSubmission->setSuppMethods(null);
        $suppMethods = 'supplemental methods';
        $this->datasetSubmission->setSuppMethods($suppMethods);
        $this->assertEquals(
            $suppMethods,
            $this->datasetSubmission->getSuppMethods()
        );
        $this->datasetSubmission->setSuppInstruments(null);
        $suppInstruments = 'supplemental instruments';
        $this->datasetSubmission->setSuppInstruments($suppInstruments);
        $this->assertEquals(
            $suppInstruments,
            $this->datasetSubmission->getSuppInstruments()
        );
        $this->datasetSubmission->setSuppSampScalesRates(null);
        $suppSampScalesRates = 'supplemental samp scales rates';
        $this->datasetSubmission->setSuppSampScalesRates($suppSampScalesRates);
        $this->assertEquals(
            $suppSampScalesRates,
            $this->datasetSubmission->getSuppSampScalesRates()
        );
        $this->datasetSubmission->setSuppErrorAnalysis(null);
        $suppErrorAnalysis = 'supplemental error analysis';
        $this->datasetSubmission->setSuppErrorAnalysis($suppErrorAnalysis);
        $this->assertEquals(
            $suppErrorAnalysis,
            $this->datasetSubmission->getSuppErrorAnalysis()
        );
        $this->datasetSubmission->setSuppProvenance(null);
        $suppProvenance = 'supplemental provenance';
        $this->datasetSubmission->setSuppProvenance($suppProvenance);
        $this->assertEquals(
            $suppProvenance,
            $this->datasetSubmission->getSuppProvenance()
        );
    }

    /**
     * Test keywords.
     *
     * @return void
     */
    public function testKeywords()
    {
        $this->assertEquals(
            [],
            $this->datasetSubmission->getThemeKeywords()
        );
        $themeKeywords = ['foo', 'bar', 'baz'];
        $this->datasetSubmission->setThemeKeywords($themeKeywords);
        $this->assertEquals(
            $themeKeywords,
            $this->datasetSubmission->getThemeKeywords()
        );
        $this->assertEquals(
            [],
            $this->datasetSubmission->getPlaceKeywords()
        );
        $placeKeywords = ['Texas', 'Louisiana', 'Mississippi'];
        $this->datasetSubmission->setPlaceKeywords($placeKeywords);
        $this->assertEquals(
            $placeKeywords,
            $this->datasetSubmission->getPlaceKeywords()
        );
        $this->assertEquals(
            [],
            $this->datasetSubmission->getTopicKeywords()
        );
        $topicKeywords = array_keys(DatasetSubmission::TOPIC_KEYWORDS);
        $this->datasetSubmission->setTopicKeywords($topicKeywords);
        $this->assertEquals(
            $topicKeywords,
            $this->datasetSubmission->getTopicKeywords()
        );
    }

    /**
     * Test bad topic keywords.
     *
     * @return void
     */
    public function testBadTopicKeywords()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->datasetSubmission->setTopicKeywords(['badTopic']);
    }

    /**
     * Test spatial extent.
     *
     * @return void
     */
    public function testSpatialExtent()
    {
        $this->datasetSubmission->setSpatialExtent(null);
        $spatialExtent = 'spatial extent';
        $this->datasetSubmission->setSpatialExtent($spatialExtent);
        $this->assertEquals(
            $spatialExtent,
            $this->datasetSubmission->getSpatialExtent()
        );
        $this->datasetSubmission->setSpatialExtentDescription(null);
        $spatialExtentDescription = 'spatial extent description';
        $this->datasetSubmission->setSpatialExtentDescription($spatialExtentDescription);
        $this->assertEquals(
            $spatialExtentDescription,
            $this->datasetSubmission->getSpatialExtentDescription()
        );
    }

    /**
     * Test temporal extent.
     *
     * @return void
     */
    public function testTemporalExtent()
    {
        $this->datasetSubmission->setTemporalExtentDesc(null);
        $temporalExtentDesc = array_keys(DatasetSubmission::TEMPORAL_EXTENT_DESCRIPTIONS)[0];
        $this->datasetSubmission->setTemporalExtentDesc($temporalExtentDesc);
        $this->assertEquals(
            $temporalExtentDesc,
            $this->datasetSubmission->getTemporalExtentDesc()
        );
        $this->datasetSubmission->setTemporalExtentBeginPosition(null);
        $temporalExtentBeginPosition = new \DateTime();
        $this->datasetSubmission->setTemporalExtentBeginPosition($temporalExtentBeginPosition);
        $this->assertSame(
            $temporalExtentBeginPosition,
            $this->datasetSubmission->getTemporalExtentBeginPosition()
        );
        $this->datasetSubmission->setTemporalExtentEndPosition(null);
        $temporalExtentEndPosition = new \DateTime();
        $this->datasetSubmission->setTemporalExtentEndPosition($temporalExtentEndPosition);
        $this->assertSame(
            $temporalExtentEndPosition,
            $this->datasetSubmission->getTemporalExtentEndPosition()
        );
    }

    /**
     * Test bad temporal extent description.
     *
     * @return void
     */
    public function testBadTemporalExtentDesc()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->datasetSubmission->setTemporalExtentDesc('bad temporal extent description');
    }

    /**
     * Test distribution format name.
     *
     * @return void
     */
    public function testDistributionFormatName()
    {
        $this->datasetSubmission->setDistributionFormatName(null);
        $distributionFormatName = 'distribution format name';
        $this->datasetSubmission->setDistributionFormatName($distributionFormatName);
        $this->assertEquals(
            $distributionFormatName,
            $this->datasetSubmission->getDistributionFormatName()
        );
    }

    /**
     * Test file decompression technique.
     *
     * @return void
     */
    public function testFileDecompressionTechnique()
    {
        $this->datasetSubmission->setFileDecompressionTechnique(null);
        $fileDecompressionTechnique = 'file decompression technique';
        $this->datasetSubmission->setFileDecompressionTechnique($fileDecompressionTechnique);
        $this->assertEquals(
            $fileDecompressionTechnique,
            $this->datasetSubmission->getFileDecompressionTechnique()
        );
    }

    /**
     * Test getting the choice list for topicKeywords.
     *
     * @return void
     */
    public function testGetTopicKeywordsChoices()
    {
        $topicKeywordsChoices = DatasetSubmission::getTopicKeywordsChoices();
        $this->assertIsArray($topicKeywordsChoices);
        foreach ($topicKeywordsChoices as $index => $value) {
            $this->assertIsString($index);
            $this->assertIsString($value);
        }
    }

    /**
     * Test getting the choice list for temporalExtentDesc.
     *
     * @return void
     */
    public function testGetTemporalExtentDescChoices()
    {
        $temporalExtentDescChoices = DatasetSubmission::getTemporalExtentDescChoices();
        $this->assertIsArray($temporalExtentDescChoices);
        foreach ($temporalExtentDescChoices as $index => $value) {
            $this->assertIsString($index);
            $this->assertIsString($value);
        }
    }

    /**
     * Test getting the choice list for restrictions.
     *
     * @return void
     */
    public function testGetRestrictionsChoices()
    {
        $restrictionsChoices = DatasetSubmission::getRestrictionsChoices();
        $this->assertIsArray($restrictionsChoices);
        foreach ($restrictionsChoices as $index => $value) {
            $this->assertIsString($index);
            $this->assertIsString($value);
        }
    }

    /**
     * Test submitting a Datatset Submission.
     *
     * @return void
     */
    public function testSubmit()
    {
        // Check for expected values for an incomplete submission.
        $this->assertEquals(
            DatasetSubmission::STATUS_INCOMPLETE,
            $this->datasetSubmission->getStatus()
        );
        $this->assertEquals(
            Dataset::DATASET_STATUS_NONE,
            $this->datasetSubmission->getDatasetStatus()
        );
        $this->assertNull($this->datasetSubmission->getSubmissionTimeStamp());
        $this->assertNull($this->datasetSubmission->getSubmitter());
        $this->datasetSubmission->setDataset($this->mockDataset);

        // Submit it.
        $this->datasetSubmission->submit($this->mockPerson);

        // Check for expected values for a complete submission.
        $this->assertEquals(
            DatasetSubmission::STATUS_COMPLETE,
            $this->datasetSubmission->getStatus()
        );
        $this->assertEquals(
            Dataset::DATASET_STATUS_SUBMITTED,
            $this->datasetSubmission->getDatasetStatus()
        );
        $this->assertInstanceOf(
            \DateTime::class,
            $this->datasetSubmission->getSubmissionTimeStamp()
        );
        $this->assertSame(
            $this->mockPerson,
            $this->datasetSubmission->getSubmitter()
        );
    }

    /**
     * Test to set review status for dataset submission.
     *
     * @return void
     */
    public function testCanSetReviewStatus()
    {
        $this->datasetSubmission->setDatasetSubmissionReviewStatus();

        $this->assertEquals(
            DatasetSubmission::STATUS_IN_REVIEW,
            $this->datasetSubmission->getStatus()
        );
    }

    /**
     * Test DatasetSubmissionReview setter and getter.
     *
     * @return void
     */
    public function testCanGetAndSetDatasetSubmissionReview()
    {
        $this->mockDatasetSubmissionReview = \Mockery::mock(
            DatasetSubmissionReview::class,
            [
                'setDatasetSubmission' => $this->datasetSubmission,
                'getReviewedBy' => $this->mockPerson,
                'getReviewStartDateTime' => new \DateTime('now'),
            ]
        );
        $this->datasetSubmission->setDatasetSubmissionReview($this->mockDatasetSubmissionReview);
        $this->assertSame($this->mockDatasetSubmissionReview, $this->datasetSubmission->getDatasetSubmissionReview());
    }

    /**
     * Test the end review functionality.
     *
     * @return void
     */
    public function testCanEndReview()
    {
        $this->datasetSubmission->setDataset($this->mockDataset);

        $testStartDateTime = new \DateTime('now');

        $datasetSubmissionReview = new DatasetSubmissionReview(
            $this->mockPerson,
            $testStartDateTime
        );

        $this->datasetSubmission->setDatasetSubmissionReview($datasetSubmissionReview);

        // End Review for the dataset submission.
        $this->datasetSubmission->reviewEvent($this->mockPerson, DatasetSubmission::DATASET_END_REVIEW);

        $this->assertEquals(DatasetSubmission::STATUS_IN_REVIEW, $this->datasetSubmission->getStatus());
    }

    /**
     * Test the setter and getter for nil reason type attribute.
     *
     * @return void
     */
    public function testCanSetAndGetTemporalExtentNilReasonType()
    {
        $mockTemporalExtentNilReasonType = 'unknown';

        $this->datasetSubmission->setTemporalExtentNilReasonType($mockTemporalExtentNilReasonType);

        $this->assertEquals(
            $mockTemporalExtentNilReasonType,
            $this->datasetSubmission->getTemporalExtentNilReasonType()
        );
    }

    /**
     * Test the adder, remover and getter for Distribution Points.
     *
     * @return void
     */
    public function testAddRemoveAndGetDistributionPoint()
    {
        // setup
        $this->mockDataCenter = \Mockery::mock(
            DataCenter::class,
            [
                'getOrganizationName' => 'GRIIDC',
                'getOrganizationUrl' => 'www.griidc.org',
            ]
        );

        $this->mockDistributionPoint = \Mockery::mock(
            DistributionPoint::class,
            [
                'setDatasetSubmission' => $this->datasetSubmission,
                'setDataCenter' => $this->mockDataCenter,
                'getDistributionUrl' => 'www.org.com',
            ]
        );

        // remove default distribution point initially created in dataset submission entity
        $defaultDistributionPoint = $this->datasetSubmission->getDistributionPoints()->first();
        if (null !== $defaultDistributionPoint) {
            $this->datasetSubmission->removeDistributionPoint($defaultDistributionPoint);
        }

        // test adder
        $this->datasetSubmission->addDistributionPoint($this->mockDistributionPoint);
        $this->assertEquals(
            1,
            $this->datasetSubmission->getDistributionPoints()->count()
        );

        // test getter
        $this->assertSame($this->datasetSubmission->getDistributionPoints()->first(), $this->mockDistributionPoint);

        // test remover
        $this->datasetSubmission->removeDistributionPoint($this->mockDistributionPoint);
        $this->assertEquals(
            0,
            $this->datasetSubmission->getDistributionPoints()->count()
        );
    }

    /**
     * Test the setter and getter for dataset file url last checked date.
     *
     * @return void
     */
    public function testCanSetAndGetDatasetFileUrlLastCheckedDate()
    {
        $dateTime = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->datasetSubmission->setDatasetFileUrlLastCheckedDate($dateTime);

        $this->assertEquals($dateTime, $this->datasetSubmission->getDatasetFileUrlLastCheckedDate());
    }

    /**
     * Test the setter and getter for dataset file url status code.
     *
     * @return void
     */
    public function testCanSetAndGetDatasetFileUrlStatusCode()
    {
        $statusCode = '200';

        $this->datasetSubmission->setDatasetFileUrlStatusCode($statusCode);

        $this->assertEquals($statusCode, $this->datasetSubmission->getDatasetFileUrlStatusCode());
    }

    /**
     * Test Remotely Hosted.
     *
     * @return void
     */
    public function testRemotelyHosted()
    {
        $remotelyHostedName = 'Remotely Hosted Name';
        $this->datasetSubmission->setRemotelyHostedName($remotelyHostedName);
        $this->assertEquals(
            $remotelyHostedName,
            $this->datasetSubmission->getRemotelyHostedName()
        );
        $this->datasetSubmission->setRemotelyHostedName(null);
        $this->assertNull($this->datasetSubmission->getRemotelyHostedName());

        $remotelyHostedName = 'Remotely Hosted Description';
        $this->datasetSubmission->setRemotelyHostedDescription($remotelyHostedName);
        $this->assertEquals(
            $remotelyHostedName,
            $this->datasetSubmission->getRemotelyHostedDescription()
        );
        $this->datasetSubmission->setRemotelyHostedDescription(null);
        $this->assertNull($this->datasetSubmission->getRemotelyHostedDescription());

        $remotelyHostedName = 'download';
        $this->datasetSubmission->setRemotelyHostedFunction($remotelyHostedName);
        $this->assertEquals(
            $remotelyHostedName,
            $this->datasetSubmission->getRemotelyHostedFunction()
        );
        $this->datasetSubmission->setRemotelyHostedFunction(null);
        $this->assertNull($this->datasetSubmission->getRemotelyHostedFunction());
    }

    /**
     * Test the adder, remover and getter for Distribution Points.
     *
     * @return void
     */
    public function testAddRemoveAndGetDatasetLink()
    {
        // setup
        $this->mockDatasetLink = \Mockery::mock(
            DatasetLink::class,
            [
                'getDatasetSubmission' => $this->datasetSubmission,
                'setDatasetSubmission' => new DatasetLink(),
                'getUrl' => 'www.bla.null',
            ]
        );

        // test adder
        $this->datasetSubmission->addDatasetLink($this->mockDatasetLink);
        $this->assertEquals(
            1,
            $this->datasetSubmission->getDatasetLinks()->count()
        );

        // remove default distribution point initially created in dataset submission entity
        $defaultDatasetLink = $this->datasetSubmission->getDatasetLinks()->first();
        if (null !== $defaultDatasetLink) {
            $this->datasetSubmission->removeDatasetLink($defaultDatasetLink);
        }

        // test adder again
        $this->datasetSubmission->addDatasetLink($this->mockDatasetLink);
        $this->assertEquals(
            1,
            $this->datasetSubmission->getDatasetLinks()->count()
        );

        // test getter
        $this->assertSame($this->datasetSubmission->getDatasetLinks()->first(), $this->mockDatasetLink);

        // test remover
        $this->datasetSubmission->removeDatasetLink($this->mockDatasetLink);
        $this->assertEquals(
            0,
            $this->datasetSubmission->getDatasetLinks()->count()
        );
    }

    /**
     * Test the setter and getter for remotely hosted url.
     *
     * @return void
     */
    public function testCanSetAndGetRemotelyHostedUrl()
    {
        $remotelyHostedUrl = 'https://path/to/dataset';

        $this->datasetSubmission->setRemotelyHostedUrl($remotelyHostedUrl);

        $this->assertEquals($remotelyHostedUrl, $this->datasetSubmission->getRemotelyHostedUrl());
    }

    /**
     * Test the setter and getter for remotely hosted url.
     *
     * @return void
     */
    public function testGetDatasetFileSize()
    {
        $fileSize = $this->datasetSubmission->getDatasetFileSize();

        $this->assertEquals($fileSize, $this->datasetSubmission->getFileset()->getZipFileSize());
    }
}
