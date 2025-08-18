<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dataset Links Entity class.
 */
#[ORM\Entity(repositoryClass: 'App\Repository\DatasetLinksRepository')]
class DatasetLink
{
    use EntityTrait;
    use EntityIdTrait;
    use EntityDateTimeTrait;

    /**
     * Valid values for self::$functionCode.
     *
     * The array keys are the values to be set in self::functionCode.
     */
    const ONLINE_FUNCTION_CODES = [
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
     * Link name codes for Link Data List.
     */
    const LINK_NAME_CODES = [
        'erddap' => [
            'name' => 'ERDDAP',
            'description' => "ERDDAP's version of the OPeNDAP .html web page for this dataset. Specify a subset of the dataset and download the data via OPeNDAP or in many different file types.",
        ],
        'ncei' => [
            'name' => 'NCEI',
            'description' => 'An NCEI link.',
        ],
    ];

    /**
     * Url for the Dataset Link.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $url;

    /**
     * Name for the Dataset Link.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $name;

    /**
     * Description for the Dataset Link.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    /**
     * Function code for the Dataset Link.
     *
     * @var string
     *
     * @see ONLINE_FUNCTION_CODES
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $functionCode;

    /**
     * Protocol for the Dataset Link.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $protocol;

    /**
     * Protocol for the Dataset Link.
     *
     * @var string
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\DatasetSubmission', inversedBy: 'datasetLinks')]
    #[ORM\JoinColumn(nullable: false)]
    protected $datasetSubmission;

    /**
     * Get the URL for the Dataset Link.
     *
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Set the URL for this Dataset Link.
     *
     * @param string $url The URL for this Dataset Link.
     *
     * @return self
     */
    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the name for the Dataset Link.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the name for this Dataset Link.
     *
     * @param string $name The name for this Dataset Link.
     *
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the description for the Dataset Link.
     *
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the description for the Dataset Link.
     *
     * @param string $description The description for the Dataset Link.
     *
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the function code for the Dataset Link.
     *
     * @return string
     */
    public function getfunctionCode(): ?string
    {
        return $this->functionCode;
    }

    /**
     * Set the function code for the Dataset Link.
     *
     * @param string $functionCode The function code for the Dataset Link.
     *
     * @return self
     */
    public function setFunctionCode(?string $functionCode): self
    {
        if (!empty($functionCode) and !in_array($functionCode, $this->getFunctionCodeChoices())) {
            throw new \InvalidArgumentException('Function Code must be one of: ' . implode(', ', $this->getFunctionCodeChoices()));
        }

        $this->functionCode = $functionCode;

        return $this;
    }

    /**
     * Get the choice list for restrictions.
     *
     * @return string|null
     */
    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    /**
     * Set the protocol for the Dataset Link.
     *
     * @param string|null $protocol The protocol for the Dataset Link.
     *
     * @return self
     */
    public function setProtocol(?string $protocol): self
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * The Dataset Submission for this Link..
     *
     * @return DatasetSubmission|null
     */
    public function getDatasetSubmission(): ?DatasetSubmission
    {
        return $this->datasetSubmission;
    }

    /**
     * Set the dataset submission for this link.
     *
     * @param DatasetSubmission|null $datasetSubmission The dataset submission for this link.
     *
     * @return self
     */
    public function setDatasetSubmission(?DatasetSubmission $datasetSubmission): self
    {
        $this->datasetSubmission = $datasetSubmission;

        return $this;
    }

    /**
     * Get the choice list for Role Code types.
     *
     * @return array
     */
    public static function getFunctionCodeChoices()
    {
        return array_flip(
            array_map(
                function ($type) {
                    return $type['name'];
                },
                static::ONLINE_FUNCTION_CODES
            )
        );
    }

    /**
     * Get the choice list for Role Code types.
     *
     * @return array
     */
    public static function getLinkNameCodeChoices()
    {
        return array_flip(
            array_map(
                function ($type) {
                    return $type['name'];
                },
                static::LINK_NAME_CODES
            )
        );
    }

    public function __toString()
    {
        return (string) $this->getName() . ' (' . $this->getUrl() . ')';
    }
}
