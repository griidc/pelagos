<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Hateoas\Configuration\Annotation as Hateoas;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * National Data Center to Data Center dataset contact association entity class.
 *
 * @ORM\Entity
 *
 * @UniqueEntity(
 *     fields={"organizationName"},
 *     errorPath="organizationName",
 *     message="A National data center with this name already exists"
 * )
 *
 * @UniqueEntity(
 *     fields={"organizationUrl"},
 *     errorPath="organizationUrl",
 *     message="A National data center with this Url already exists"
 * )
 *
 * @Hateoas\Relation(
 *   "self",
 *   href = @Hateoas\Route(
 *     "pelagos_api_national_data_center_get",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "edit",
 *   href = @Hateoas\Route(
 *     "pelagos_api_national_data_center_put",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not service('security.authorization_checker').isGranted(['CAN_EDIT'], object))"
 *   )
 * )
 * @Hateoas\Relation(
 *   "delete",
 *   href = @Hateoas\Route(
 *     "pelagos_api_national_data_center_delete",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not object.isDeletable() or not service('security.authorization_checker').isGranted(['CAN_DELETE'], object))"
 *   )
 * )
 */
class NationalDataCenter extends DataCenter
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'National Data Center';

    /**
     * Whether this entity is a national data center, or not.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $nationalCenter;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->nationalCenter = true;
    }
}
