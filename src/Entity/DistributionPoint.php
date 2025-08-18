<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DatasetSubmission to Data Center association abstract class.
 */
#[ORM\Entity]
class DistributionPoint
{
    use EntityTrait;
    use EntityIdTrait;
    use EntityDateTimeTrait;

    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Dataset Submission to Data Center Association';

    /**
     * Valid values for self::$roleCode.
     *
     * The array keys are the values to be set in self::roleCode.
     */
    const ROLECODES = [
        'distributor' => [
            'name' => 'Distributor',
            'description' => 'The organization that is responsible for providing the PARR required access to the data',
        ],
        'author' => [
            'name' => 'Author',
            'description' => 'Party who authored the resource.',
        ],
        'custodian' => [
            'name' => 'Custodian',
            'description' => 'The individual/organization that has
                accountability and responsibility for the data.',
        ],
        'originator' => [
            'name' => 'Originator',
            'description' => 'the name of the individual or organization who is responsible
                  for the data at the point when the data was first created.',
        ],
        'owner' => [
            'name' => 'Owner',
            'description' => 'The individual or organization that has ownership of the resource.',
        ],
        'pointOfContact' => [
            'name' => 'Point of Contact',
            'description' => 'Party who can be contacted for acquiring knowledge ' .
                'about or acquisition of the resource.',
        ],
        'principalInvestigator' => [
            'name' => 'Principal Investigator',
            'description' => 'Key party responsible for gathering information and conducting research.',
        ],
        'processor' => [
            'name' => 'Processor',
            'description' => 'The name of the individual or organization who
                has processed the data in a manner such that the resource has been modified.',
        ],
        'publisher' => [
            'name' => 'Publisher',
            'description' => 'The individual or organization who prepares and issues the resource.',
        ],
        'resourceProvider' => [
            'name' => 'Resource Provider',
            'description' => 'The individual or organization that supplies
                or allocates the resource for another entity.',
        ],
        'user' => [
            'name' => 'User',
            'description' => 'The individuals or organizations who are the intended consumers of the resource.',
        ],
    ];

    /**
     * The Dataset Submission for this association.
     *
     * @var DatasetSubmission
     *
     */
    #[ORM\ManyToOne(targetEntity: 'DatasetSubmission', inversedBy: 'distributionPoints')]
    #[Assert\NotBlank(message: 'Dataset Submission is required')]
    protected $datasetSubmission;

    /**
     * The Distribution Contact (Data Center) for this association.
     *
     * @var DataCenter
     *
     */
    #[ORM\ManyToOne(targetEntity: 'DataCenter')]
    #[Assert\NotBlank(message: 'Distribution Contact is required')]
    protected $dataCenter;

    /**
     * The distribution Url for this association.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $distributionUrl;

    /**
     *  The Role Code for this association (CI_RoleCode).
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $roleCode;

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
     * @param DataCenter $dataCenter The distribution contact for this association.
     *
     * @return void
     */
    public function setDataCenter(DataCenter $dataCenter)
    {
        $this->dataCenter = $dataCenter;
    }

    /**
     * Getter for distribution contact.
     *
     * @return dataCenter
     */
    public function getDataCenter()
    {
        return $this->dataCenter;
    }

    /**
     * Setter for distribution url.
     *
     * @param string|null $distributionUrl The distribution url for this association.
     *
     * @return void
     */
    public function setDistributionUrl(?string $distributionUrl = null)
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

    /**
     * Getter for distribution url protocol.
     *
     * @return string
     */
    public function getDistributionUrlProtocol()
    {
        if ($this->distributionUrl !== null) {
            preg_match('/^(.*?):.*$/', $this->distributionUrl, $matches);
            return $matches[1];
        } else {
            return null;
        }
    }

    /**
     * Setter for role code.
     *
     * @param string $roleCode The CI_ROLECODE for this association.
     *
     * @throws \InvalidArgumentException When $roleCode is not a valid value.
     *
     * @return void
     */
    public function setRoleCode(string $roleCode = null)
    {
        if (!array_key_exists($roleCode, static::ROLECODES) and $roleCode !== null) {
            throw new \InvalidArgumentException("$roleCode is not a valid value for DistributionPoint::\$roleCode");
        }
        $this->roleCode = $roleCode;
    }

    /**
     * Getter for role code.
     *
     * @return string
     */
    public function getRoleCode()
    {
        return $this->roleCode;
    }

    /**
     * Get the choice list for Role Code types.
     *
     * @return array
     */
    public static function getRoleCodeChoices()
    {
        return array_flip(
            array_map(
                function ($type) {
                    return $type['name'];
                },
                static::ROLECODES
            )
        );
    }

    public function __toString(): string
    {
        return (string) $this->getDataCenter();
    }
}
