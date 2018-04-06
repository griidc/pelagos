<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DatasetSubmission to National Data Center association abstract class.
 *
 * @ORM\Entity
 */
class DistributionPoint extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Dataset Submission to National Data Center Association';

    /**
     * The Dataset Submission for this association.
     *
     * @var DatasetSubmission
     *
     * @ORM\ManyToOne(targetEntity="DatasetSubmission", inversedBy="distributionPoint")
     *
     * @Assert\NotBlank(
     *     message="Dataset Submission is required")
     */
    protected $datasetSubmission;

    /**
     * The Distribution Contact (National Data Center) for this association.
     *
     * @var NationalDataCenter
     *
     * @ORM\ManyToOne(targetEntity="NationalDataCenter", inversedBy="distributionPoint")
     *
     * @Assert\NotBlank(
     *     message="Distribution Contact (National Data Center) is required")
     */
    protected $nationalDataCenter;

    /**
     * The distribution Url for this association.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $distributionUrl;

    /**
     * Settter for datasetSubmission.
     *
     * @param DatasetSubmission $datasetSubmission The Dataset Submission for this association.
     *
     * @return void
     */
    public function setDatasetSubmission(DatasetSubmission $datasetSubmission = null)
    {
        $this->datasetSubmission = $datasetSubmission;
    }

    /**
     * Getter for datasetSubmission.
     *
     * @return DatasetSubmission
     */
    public function getDatasetSubmission()
    {
        return $this->datasetSubmission;
    }

    /**
     * Setter for distribution contact.
     *
     * @param NationalDataCenter $nationalDataCenter The distribution contact for this association.
     *
     * @return void
     */
    public function setNationalDataCenter(NationalDataCenter $nationalDataCenter)
    {
        $this->nationalDataCenter = $nationalDataCenter;
    }

    /**
     * Getter for distribution contact.
     *
     * @return NationalDataCenter
     */
    public function getNationalDataCenter()
    {
        return $this->nationalDataCenter;
    }

    /**
     * Setter for distribution url.
     *
     * @param string $distributionUrl The distribution url for this association.
     *
     * @return void
     */
    public function setDistributionUrl($distributionUrl = null)
    {
        $this->distributionUrl = $distributionUrl;
    }

    /**
     * Getter for distribution url.
     *
     * @return string
     */
    public function getDistributionUrl()
    {
        return $this->distributionUrl;
    }
}
