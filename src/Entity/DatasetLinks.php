<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dataset Links Entity class.
 *
 * @ORM\Entity(repositoryClass="App\Repository\DatasetLinksRepository")
 */
class DatasetLinks extends Entity
{
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
     * Url for the Dataset Link.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $url;

    /**
     * Name for the Dataset Link.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $name;

    /**
     * Description for the Dataset Link.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * Function code for the Dataset Link.
     *
     * @see ONLINE_FUNCTION_CODES
     
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $functionCode;

    /**
     * Protocol for the Dataset Link.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $protocol;

    /**
     * Get the URL for the Dataset Link.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set the URL for this Dataset Link.
     *
     * @param string $url The URL for this Dataset Link.
     *
     * @return void
     */
    public function setUrl(string $url): self
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
    public function setName(string $name): self
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
     * @param string $string The description for the Dataset Link.
     *
     * @return self
     */
    public function setDescription(string $string): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the function code for the Dataset Link.
     *
     * @return string
     */
    public function getfunctionCode(): string
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
    public function setFunctionCode(string $functionCode): self
    {
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
}
