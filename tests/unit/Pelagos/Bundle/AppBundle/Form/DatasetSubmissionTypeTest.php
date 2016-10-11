<?php

namespace Tests\unit\Pelagos\Bundle\AppBundle\Form;

use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Person;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;
use Pelagos\Entity\PersonDatasetSubmissionMetadataContact;

/**
 * Tests for Form/DatasetSubmissionType.
 *
 * @group Pelagos
 * @group Pelagos\Bundle\AppBundle\Form
 * @group Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType
 */
class DatasetSubmissionTypeTest extends FormTypeTestCase
{
    /**
     * Dataset Submission entity to test with.
     *
     * @var DatasetSubmission
     */
    protected $datasetSubmission;

    /**
     * Return the entities to map with the test entity manager.
     *
     * @return array
     */
    protected function getEntities()
    {
        return array_merge(
            parent::getEntities(),
            array(
                DatasetSubmission::class,
                PersonDatasetSubmissionDatasetContact::class,
                PersonDatasetSubmissionMetadataContact::class,
                Person::class,
            )
        );
    }

    /**
     * Set up for each test.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->form = $this->factory->create(DatasetSubmissionType::class);
        $this->datasetSubmission = new DatasetSubmission;
    }

    /**
     * Test submitting the form with no data.
     *
     * @return void
     */
    public function testSubmitNoData()
    {
        $this->form->submit(array());
        // Make sure an empty form does not change any default values.
        $this->assertEquals($this->datasetSubmission, $this->form->getData());
    }

    /**
     * Test submitting the form with valid data.
     *
     * @return void
     */
    public function testSubmitValidData()
    {
        // Some form data for testing.
        $formData = array(
            'title' => 'test title',
            'abstract' => 'test abstract',
            'authors' => 'test authors',
            'restrictions' => DatasetSubmission::RESTRICTION_NONE,
            'doi' => 'test doi',
            'datasetFileTransferType' => 'test dataset file transfer type',
            'metadataFileTransferType' => 'test metadata file transfer type',
            'shortTitle' => 'test short title',
            'referenceDate' => '2000-01-01',
            'referenceDateType' => array_keys(DatasetSubmission::REFERENCE_DATE_TYPES)[0],
            'purpose' => 'test purpose',
            'suppParams' => 'test supplemental parameters',
            'suppMethods' => 'test supplemental methods',
            'suppInstruments' => 'test supplemental instruments',
            'suppSampScalesRates' => 'test supplemental sampling scales and rates',
            'suppErrorAnalysis' => 'test supplemental error analysis',
            'suppProvenance' => 'test supplemental provenance',
            'themeKeywords' => array('themeFoo','themeBar','themeBaz'),
            'placeKeywords' => array('placeFoo','placeBar','placeBaz'),
            'topicKeywords' => array_keys(DatasetSubmission::TOPIC_KEYWORDS),
            'spatialExtent' => 'test spatial extent',
            'temporalExtentDesc' => array_keys(DatasetSubmission::TEMPORAL_EXTENT_DESCRIPTIONS)[0],
            'temporalExtentBeginPosition' => '2000-01-02',
            'temporalExtentEndPosition' => '2000-01-03',
            'datasetContacts' => array(
                array(
                    'role' => 'pointOfContact',
                ),
            ),
            'metadataContacts' => array(
                array(
                    'role' => 'pointOfContact',
                ),
            ),
        );

        // Create a form view.
        $formView = $this->form->createView();
        // Loop though the form data.
        foreach (array_keys($formData) as $key) {
            // Make sure the form has fields for all the form data.
            $this->assertArrayHasKey($key, $formView->children);
        }

        // Submit the form.
        $this->form->submit($formData);
        // Make sure the form was able to process the form data.
        $this->assertTrue($this->form->isSynchronized());

        // Convert date strings to DateTimes.
        $formData['referenceDate'] = new \DateTime($formData['referenceDate']);
        $formData['temporalExtentBeginPosition'] = new \DateTime($formData['temporalExtentBeginPosition']);
        $formData['temporalExtentEndPosition'] = new \DateTime($formData['temporalExtentEndPosition']);

        // Convert dataset contacts to PersonDatasetSubmissionDatasetContacts.
        $personDatasetSubmissionDatasetContact = new PersonDatasetSubmissionDatasetContact();
        $personDatasetSubmissionDatasetContact->setRole($formData['datasetContacts'][0]['role']);
        $formData['datasetContacts'] = array($personDatasetSubmissionDatasetContact);

        // Convert metadata contacts to PersonDatasetSubmissionMetadataContacts.
        $personMetadataSubmissionMetadataContact = new PersonDatasetSubmissionMetadataContact();
        $personMetadataSubmissionMetadataContact->setRole($formData['metadataContacts'][0]['role']);
        $formData['metadataContacts'] = array($personMetadataSubmissionMetadataContact);

        // Create a DatasetSubmission.
        $datasetSubmission = new DatasetSubmission;
        // Set properties to match form data.
        foreach ($formData as $property => $value) {
            $this->propertyAccessor->setValue($datasetSubmission, $property, $value);
        }
        // Compare with the entity created by the form.
        $this->assertEquals($datasetSubmission, $this->form->getData());
    }
}
