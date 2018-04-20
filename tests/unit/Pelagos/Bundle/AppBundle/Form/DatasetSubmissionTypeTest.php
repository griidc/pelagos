<?php

namespace Tests\unit\Pelagos\Bundle\AppBundle\Form;

use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DIF;
use Pelagos\Entity\Person;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;
use Pelagos\Entity\PersonDatasetSubmissionMetadataContact;
use Symfony\Component\Form\PreloadedExtension;

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
        $this->testDif = new DIF;
        $this->testDif->setDataset(new Dataset);
        $this->testPersonDatasetSubmissionDatasetContact = new PersonDatasetSubmissionDatasetContact;

        parent::setUp();
    }

    /**
     * Test submitting the form with no data.
     *
     * @return void
     */
    public function testSubmitNoData()
    {
        //This test fails because Symfony's EntityType isn't supported in phpunit.
        /*
        $datasetSubmission = new DatasetSubmission($this->testDif, $this->testPersonDatasetSubmissionDatasetContact);

        $form = $this->factory->create(DatasetSubmissionType::class);
        $form->submit(array());

        // Set this to null for purposes of this test.
        $datasetSubmission->setRestrictions(null);
        // Wipe out Dataset Contacts for test against blank form.
        $datasetSubmission->getDatasetContacts()->clear();
        $datasetSubmission->getMetadataContacts()->clear();

        // Make sure an empty form does not change any default values.
        $this->assertEquals($datasetSubmission, $form->getData());

        */
    }

    /**
     * Test submitting the form with valid data.
     *
     * @return void
     */
    public function testSubmitValidData()
    {
        //This test fails because Symfony's EntityType isn't supported in phpunit.
        /*
        $form = $this->factory->create(DatasetSubmissionType::class);

        // Some form data for testing.
        $formData = array(
            'title' => 'test title',
            'abstract' => 'test abstract',
            'authors' => 'test authors',
            'restrictions' => DatasetSubmission::RESTRICTION_NONE,
            'datasetFileTransferType' => 'test dataset file transfer type',
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
            'spatialExtentDescription' => 'test spatial extent description',
            'temporalExtentDesc' => array_keys(DatasetSubmission::TEMPORAL_EXTENT_DESCRIPTIONS)[0],
            'temporalExtentBeginPosition' => '2000-01-02',
            'temporalExtentEndPosition' => '2000-01-03',
            'distributionFormatName' => 'test distribution format name',
            'fileDecompressionTechnique' => 'test file decompression technique',
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
        $formView = $form->createView();
        // Loop though the form data.
        foreach (array_keys($formData) as $key) {
            // Make sure the form has fields for all the form data.
            $this->assertArrayHasKey($key, $formView->children);
        }

        // Submit the form.
        $form->submit($formData);
        // Make sure the form was able to process the form data.
        $this->assertTrue($form->isSynchronized());

        // Convert date strings to DateTimes.
        $formData['referenceDate'] = new \DateTime($formData['referenceDate'], new \DateTimeZone('UTC'));
        $formData['temporalExtentBeginPosition'] = new \DateTime($formData['temporalExtentBeginPosition'], new \DateTimeZone('UTC'));
        $formData['temporalExtentEndPosition'] = new \DateTime($formData['temporalExtentEndPosition'], new \DateTimeZone('UTC'));

        // Convert dataset contacts to PersonDatasetSubmissionDatasetContacts.
        $personDatasetSubmissionDatasetContact = new PersonDatasetSubmissionDatasetContact();
        $personDatasetSubmissionDatasetContact->setRole($formData['datasetContacts'][0]['role']);
        $formData['datasetContacts'] = array($personDatasetSubmissionDatasetContact);
        
        // Convert metadata contacts to PersonDatasetSubmissionMetadataContacts.
        $personMetadataSubmissionMetadataContact = new PersonDatasetSubmissionMetadataContact();
        $personMetadataSubmissionMetadataContact->setRole($formData['metadataContacts'][0]['role']);
        $formData['metadataContacts'] = array($personMetadataSubmissionMetadataContact);

        // Create a DatasetSubmission.
        $datasetSubmission = new DatasetSubmission($this->testDif, $this->testPersonDatasetSubmissionDatasetContact);
        // Set properties to match form data.
        foreach ($formData as $property => $value) {
            $this->propertyAccessor->setValue($datasetSubmission, $property, $value);
        }
        // Compare with the entity created by the form.
        $this->assertEquals($datasetSubmission, $form->getData());

        */
    }

    /**
     * Override parent's class, include our own PreloadeExtenion.
     *
     * @return array
     */
    protected function getExtensions()
    {
        $type = new DatasetSubmissionType($this->testDif, $this->testPersonDatasetSubmissionDatasetContact);

        return array_merge(parent::getExtensions(), array(
            new PreLoadedExtension(array($type), array()),
        ));
    }
}
