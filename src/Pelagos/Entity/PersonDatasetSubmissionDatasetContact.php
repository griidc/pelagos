<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person to Dataset Submission dataset contact association entity class.
 *
 * @ORM\Entity
 */
class PersonDatasetSubmissionDatasetContact extends PersonDatasetSubmission
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Dataset Contact';

    /**
     * The Dataset Submission for this association.
     *
     * @var DatasetSubmission
     *
     * @ORM\ManyToOne(targetEntity="DatasetSubmission", inversedBy="datasetContacts")
     */
    protected $datasetSubmission;

    /**
     * Whether this entity is a primary contact, or not.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $primaryContact;

    /**
     * Getter for primary contact indicator.
     *
     * @return boolean
     */
    public function isPrimaryContact()
    {
        return $this->primaryContact;
    }

    /**
     * Setter for primary contact indicator. This is intented to be protected.
     *
     * @param boolean|null $state Which state to set.
     *
     * @throws \Exception If method is called with anything other than a bool or a null.
     *
     * @return void
     */
    public function setPrimaryContact($state)
    {
        if (is_bool($state) or null === $state) {
            $this->primaryContact = $state;
        } else {
            throw new \Exception('setPrimaryContact method called with invalid parameter');
        }
    }
}
