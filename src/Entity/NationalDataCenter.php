<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * National Data Center to Data Center dataset contact association entity class.
 *
 */
#[ORM\Entity]
#[UniqueEntity(fields: ['organizationName'], errorPath: 'organizationName', message: 'A National data center with this name already exists')]
#[UniqueEntity(fields: ['organizationUrl'], errorPath: 'organizationUrl', message: 'A National data center with this Url already exists')]
class NationalDataCenter extends DataCenter
{
    use IdTrait;

    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'National Data Center';

    /**
     * Whether this entity is a national data center, or not.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $nationalCenter;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->nationalCenter = true;
    }
}
