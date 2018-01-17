<?php

namespace Pelagos\Entity;

use Doctrine\Common\Collections\Collection;

/**
 * Unit tests for Pelagos\Entity\DatasetSubmission.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\DatasetSubmission
 *
 * @package Pelagos\Entity
 */
class DatasetSubmissionTest extends \PHPUnit_Framework_TestCase
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
            array(
                'setDatasetSubmission' => null,
                'setDatasetSubmissionStatus' => null,
                'setMetadataStatus' => null,
                'setAvailabilityStatus' => null,
                'updateAvailabilityStatus' => null,
                'getUdi' => 'T1.x123.000:0001',
            )
        );
        $this->mockPerson = \Mockery::mock(
            Person::class,
            array(
                'getLastName' => 'Person',
                'getFirstName' => 'Mock',
                'getEmailAddress' => 'mock.person@test.null',
            )
        );
        $this->mockDif = \Mockery::mock(
            DIF::class,
            array(
                'getDataset' => $this->mockDataset,
                'getTitle' => 'Title from DIF',
                'getAbstract' => 'Abstract from DIF',
                'getVariablesObserved' => null,
                'getSpatialExtentGeometry' => null,
                'getSpatialExtentDescription' => null,
                'getPrimaryPointOfContact' => $this->mockPerson,

            )
        );
        $this->mockPersonDatasetSubmissionDatasetContact = \Mockery::mock(
            PersonDatasetSubmissionDatasetContact::class,
            array(
                'setDatasetSubmission' => null,
                'getPerson' => $this->mockPerson,
                'setPerson' => null,
                'setRole' => null,
                'setPrimaryContact' => null
            )
        );
        $this->datasetSubmission = new DatasetSubmission($this->mockDif, $this->mockPersonDatasetSubmissionDatasetContact);
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
     * Test attempting to set a bad sequence.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testBadSequence()
    {
        $this->datasetSubmission->setSequence('42');
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
        $this->datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
        $this->assertEquals(
            DatasetSubmission::TRANSFER_STATUS_COMPLETED,
            $this->datasetSubmission->getDatasetFileTransferStatus()
        );
        $this->datasetSubmission->setDatasetFileName('foobar.baz');
        $this->assertEquals(
            'foobar.baz',
            $this->datasetSubmission->getDatasetFileName()
        );
        $this->datasetSubmission->setDatasetFileSize(1234);
        $this->assertEquals(
            1234,
            $this->datasetSubmission->getDatasetFileSize()
        );
        $this->datasetSubmission->setDatasetFileMd5Hash('deadbeef');
        $this->assertEquals(
            'deadbeef',
            $this->datasetSubmission->getDatasetFileMd5hash()
        );
        $this->datasetSubmission->setDatasetFileSha1Hash('decaf');
        $this->assertEquals(
            'decaf',
            $this->datasetSubmission->getDatasetFileSha1hash()
        );
        $this->datasetSubmission->setDatasetFileSha256Hash('cafe');
        $this->assertEquals(
            'cafe',
            $this->datasetSubmission->getDatasetFileSha256hash()
        );
    }

    /**
     * Test metadata file attributes.
     *
     * @return void
     */
    public function testMetadataFileAttributes()
    {
        $this->datasetSubmission->setMetadataFileTransferType(DatasetSubmission::TRANSFER_TYPE_UPLOAD);
        $this->assertEquals(
            DatasetSubmission::TRANSFER_TYPE_UPLOAD,
            $this->datasetSubmission->getMetadataFileTransferType()
        );
        $this->datasetSubmission->setMetadataFileUri('foobar');
        $this->assertEquals(
            'foobar',
            $this->datasetSubmission->getMetadataFileUri()
        );
        $this->datasetSubmission->setMetadataFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
        $this->assertEquals(
            DatasetSubmission::TRANSFER_STATUS_COMPLETED,
            $this->datasetSubmission->getMetadataFileTransferStatus()
        );
        $this->datasetSubmission->setMetadataFileName('foobar.baz');
        $this->assertEquals(
            'foobar.baz',
            $this->datasetSubmission->getMetadataFileName()
        );
        $this->datasetSubmission->setMetadataFileSha256Hash('cafe');
        $this->assertEquals(
            'cafe',
            $this->datasetSubmission->getMetadataFileSha256hash()
        );
    }

    /**
     * Test metadata status.
     *
     * @return void
     */
    public function testMetadataStatus()
    {
        $this->datasetSubmission->setMetadataStatus(DatasetSubmission::METADATA_STATUS_ACCEPTED);
        $this->assertEquals(
            DatasetSubmission::METADATA_STATUS_ACCEPTED,
            $this->datasetSubmission->getMetadataStatus()
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
     * Test reference date and type.
     *
     * @return void
     */
    public function testReferenceDateAndType()
    {
        $this->datasetSubmission->setReferenceDate(null);
        $referenceDate = new \DateTime;
        $this->datasetSubmission->setReferenceDate($referenceDate);
        $this->assertSame(
            $referenceDate,
            $this->datasetSubmission->getReferenceDate()
        );
        $referenceDateType = array_keys(DatasetSubmission::REFERENCE_DATE_TYPES)[0];
        $this->datasetSubmission->setReferenceDateType($referenceDateType);
        $this->assertEquals(
            $referenceDateType,
            $this->datasetSubmission->getReferenceDateType()
        );
    }

    /**
     * Test attempting to set a bad referenceDateType.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testBadReferenceDateType()
    {
        $this->datasetSubmission->setReferenceDateType('foobar');
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
            array(),
            $this->datasetSubmission->getThemeKeywords()
        );
        $themeKeywords = array('foo','bar','baz');
        $this->datasetSubmission->setThemeKeywords($themeKeywords);
        $this->assertEquals(
            $themeKeywords,
            $this->datasetSubmission->getThemeKeywords()
        );
        $this->assertEquals(
            array(),
            $this->datasetSubmission->getPlaceKeywords()
        );
        $placeKeywords = array('Texas','Louisiana','Mississippi');
        $this->datasetSubmission->setPlaceKeywords($placeKeywords);
        $this->assertEquals(
            $placeKeywords,
            $this->datasetSubmission->getPlaceKeywords()
        );
        $this->assertEquals(
            array(),
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
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testBadTopicKeywords()
    {
        $this->datasetSubmission->setTopicKeywords(array('badTopic'));
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
        $temporalExtentBeginPosition = new \DateTime;
        $this->datasetSubmission->setTemporalExtentBeginPosition($temporalExtentBeginPosition);
        $this->assertSame(
            $temporalExtentBeginPosition,
            $this->datasetSubmission->getTemporalExtentBeginPosition()
        );
        $this->datasetSubmission->setTemporalExtentEndPosition(null);
        $temporalExtentEndPosition = new \DateTime;
        $this->datasetSubmission->setTemporalExtentEndPosition($temporalExtentEndPosition);
        $this->assertSame(
            $temporalExtentEndPosition,
            $this->datasetSubmission->getTemporalExtentEndPosition()
        );
    }

    /**
     * Test bad temporal extent description.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testBadTemporalExtentDesc()
    {
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
     * Test update dataset submission status.
     *
     * @return void
     */
    public function testUpdateDatasetSubmissionStatus()
    {
        $this->datasetSubmission->setDataset($this->mockDataset);
        $this->datasetSubmission->setDatasetFileUri(null);
        $this->datasetSubmission->setDatasetFileUri('foo');
    }

    /**
     * Test update availability status.
     *
     * @return void
     */
    public function testUpdateAvailabilityStatus()
    {
        // datasetFileTransferStatus == TRANSFER_STATUS_COMPLETED
        $this->datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
        $this->datasetSubmission->setDataset($this->mockDataset);
        $this->datasetSubmission->setMetadataStatus(DatasetSubmission::METADATA_STATUS_ACCEPTED);
        $this->datasetSubmission->setRestrictions(DatasetSubmission::RESTRICTION_NONE);
        $this->datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
        $this->datasetSubmission->setRestrictions(DatasetSubmission::RESTRICTION_RESTRICTED);
        $this->datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
        $this->datasetSubmission->setMetadataStatus(DatasetSubmission::METADATA_STATUS_NONE);
        $this->datasetSubmission->setMetadataFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
        $this->datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
        $this->datasetSubmission->setMetadataFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
        $this->datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
        // datasetFileTransferStatus == TRANSFER_STATUS_REMOTELY_HOSTED
        $this->datasetSubmission->setMetadataStatus(DatasetSubmission::METADATA_STATUS_ACCEPTED);
        $this->datasetSubmission->setRestrictions(DatasetSubmission::RESTRICTION_NONE);
        $this->datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED);
        $this->datasetSubmission->setRestrictions(DatasetSubmission::RESTRICTION_RESTRICTED);
        $this->datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED);
        $this->datasetSubmission->setMetadataStatus(DatasetSubmission::METADATA_STATUS_NONE);
        $this->datasetSubmission->setMetadataFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
        $this->datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED);
        $this->datasetSubmission->setMetadataFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
        $this->datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED);
    }

    /**
     * Test getting the choice list for referenceDateType.
     *
     * @return void
     */
    public function testGetReferenceDateTypeChoices()
    {
        $referenceDateTypeChoices = DatasetSubmission::getReferenceDateTypeChoices();
        $this->assertInternalType('array', $referenceDateTypeChoices);
        foreach ($referenceDateTypeChoices as $index => $value) {
            $this->assertInternalType('string', $index);
            $this->assertInternalType('string', $value);
        }
    }

    /**
     * Test getting the choice list for topicKeywords.
     *
     * @return void
     */
    public function testGetTopicKeywordsChoices()
    {
        $topicKeywordsChoices = DatasetSubmission::getTopicKeywordsChoices();
        $this->assertInternalType('array', $topicKeywordsChoices);
        foreach ($topicKeywordsChoices as $index => $value) {
            $this->assertInternalType('string', $index);
            $this->assertInternalType('string', $value);
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
        $this->assertInternalType('array', $temporalExtentDescChoices);
        foreach ($temporalExtentDescChoices as $index => $value) {
            $this->assertInternalType('string', $index);
            $this->assertInternalType('string', $value);
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
        $this->assertInternalType('array', $restrictionsChoices);
        foreach ($restrictionsChoices as $index => $value) {
            $this->assertInternalType('string', $index);
            $this->assertInternalType('string', $value);
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
            DatasetSubmission::METADATA_STATUS_NONE,
            $this->datasetSubmission->getMetadataStatus()
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
            DatasetSubmission::METADATA_STATUS_SUBMITTED,
            $this->datasetSubmission->getMetadataStatus()
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
}
