<?php

namespace App\Entity;

use App\Exception\NotDeletableException;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Abstract class that contains basic properties and methods common to all Pelagos entities.
 */
#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['id'], errorPath: 'id', message: 'This id has already been assigned', groups: ['unique_id'])]
abstract class Entity
{
    /**
     * A friendly name for this type of entity.
     */
    public const FRIENDLY_NAME = 'Pelagos Entity';

    /**
     * Entity identifier.
     *
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Serializer\Groups(["id", "search"])]
    #[Groups(["id", "search"])]
    #[Assert\Range(min: 1, max: 2147483647, notInRangeMessage: 'ID must be in between 1 and 2147483647', invalidMessage: 'ID must be a positive integer', groups: ['id'])]
    protected $id;

    /**
     * The Person who created this Entity.
     *
     * @var Person;
     *
     */
    #[ORM\ManyToOne(targetEntity: 'Person')]
    #[Serializer\Exclude]
    protected $creator;

    /**
     * The creation time stamp (in UTC) for this Entity.
     *
     * @var \DateTime $creationTimeStamp;
     */
    #[ORM\Column(type: 'datetimetz')]
    protected $creationTimeStamp;

    /**
     * The last modification time stamp (in UTC) for this Entity.
     *
     * @var \DateTime $modificationTimeStamp;
     */
    #[ORM\Column(type: 'datetimetz')]
    protected $modificationTimeStamp;

    /**
     * The Person who last modified this Entity.
     *
     * @var Person
     *
     */
    #[ORM\ManyToOne(targetEntity: 'Person')]
    #[Serializer\Exclude]
    protected $modifier;

    /**
     * The time zone to use when returning time stamps.
     *
     * @var string
     */
    protected $timeZone = 'UTC';

    /**
     * Setter for identifier.
     *
     * @param int|null $id this entity's Identifier
     *
     * @return void
     *
     * @throws \InvalidArgumentException when $id id not an integer or null
     */
    public function setId($id = null)
    {
        // Must be an integer or null.
        if (null !== $id and !is_numeric($id)) {
            throw new \InvalidArgumentException('id must be an integer or null');
        }
        // Can only change from or to null.
        if (null === $this->id or null === $id) {
            $this->id = $id;
        }
    }

    /**
     * Getter for id property.
     *
     * @return int persistent identifier for the Entity
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setter for creator.
     *
     * @param Person $creator this entity's creator
     *
     * @return void
     */
    public function setCreator(Person $creator)
    {
        $this->creator = $creator;
        $this->modifier = $creator;
    }

    /**
     * Getter for creator.
     *
     * @return Person this entity's creator
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Setter for modifier property.
     *
     * @param Person $modifier the Person who last modified this Entity
     *
     * @return void
     */
    public function setModifier(Person $modifier)
    {
        $this->modifier = $modifier;
    }

    /**
     * Getter for modifier property.
     *
     * @return Person the Person who modified this Entity
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * Setter for creationTimeStamp property.
     *
     * @param \DateTime $timeStamp creation time stamp to set
     *
     * @return void
     *
     * @throws \Exception when $timeStamp does not have a timezone of UTC
     */
    public function setCreationTimeStamp(\DateTime $timeStamp = null)
    {
        if (isset($timeStamp)) {
            if ('UTC' != $timeStamp->getTimezone()->getName()) {
                throw new \Exception('creationTimeStamp must be in UTC');
            }
            $this->creationTimeStamp = $timeStamp;
        } else {
            $this->creationTimeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        }
        $this->setModificationTimeStamp(clone $this->getCreationTimeStamp());
    }

    /**
     * Getter for creationTimeStamp property.
     *
     * The default is to return the time stamp in the time zone set in $this->timeZone.
     * Setting $localized to true will return the time stamp localized to the current time zone.
     *
     * @param bool $localized whether to convert time stamp to the local time zone
     *
     * @return \DateTime creation time stamp for this Person
     */
    public function getCreationTimeStamp(bool $localized = false)
    {
        if (!isset($this->creationTimeStamp)) {
            return null;
        }
        $timeStamp = clone $this->creationTimeStamp;
        if ($localized) {
            $timeStamp->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
        } else {
            $timeStamp->setTimeZone(new \DateTimeZone($this->timeZone));
        }

        return $timeStamp;
    }

    /**
     * Get the creationTimeStamp property as an ISO8601 string.
     *
     * @param bool $localized whether to convert time stamp to the local timezone
     *
     * @return string ISO8601 string representing creationTimeStamp
     */
    public function getCreationTimeStampAsISO(bool $localized = false)
    {
        if (isset($this->creationTimeStamp) and $this->creationTimeStamp instanceof \DateTime) {
            return $this->getCreationTimeStamp($localized)->format(\DateTime::ISO8601);
        }

        return null;
    }

    /**
     * Update the time stamps to the current time.
     *
     * The creation time stamp is only updated if not already set.
     *
     *
     * @return void
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimeStamps()
    {
        if (null == $this->creationTimeStamp) {
            $this->setCreationTimeStamp();
        }
        $this->setModificationTimeStamp();
    }

    /**
     * Setter for modificationTimeStamp property.
     *
     * @param \DateTime $timeStamp modification time stamp to set
     *
     * @return void
     *
     * @throws \Exception when $timeStamp does not have a timezone of UTC
     */
    public function setModificationTimeStamp(\DateTime $timeStamp = null)
    {
        if (isset($timeStamp)) {
            if ('UTC' != $timeStamp->getTimezone()->getName()) {
                throw new \Exception('modificationTimeStamp must be in UTC');
            }
            $this->modificationTimeStamp = $timeStamp;
        } else {
            $this->modificationTimeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        }
    }

    /**
     * Getter for modificationTimeStamp property.
     *
     * The default is to return the time stamp in the time zone set in $this->timeZone.
     *
     * @param bool $localized whether to convert time stamp to the local time zone
     *
     * @return \DateTime modification time stamp for this Person
     */
    public function getModificationTimeStamp(bool $localized = false)
    {
        if (!isset($this->modificationTimeStamp)) {
            return null;
        }
        $timeStamp = clone $this->modificationTimeStamp;
        if ($localized) {
            $timeStamp->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
        } else {
            $timeStamp->setTimeZone(new \DateTimeZone($this->timeZone));
        }

        return $timeStamp;
    }

    /**
     * Get the modificationTimeStamp property as an ISO8601 string.
     *
     * @param bool $localized whether to convert time stamp to the local timezone
     *
     * @return string ISO8601 string representing modificationTimeStamp
     */
    public function getModificationTimeStampAsISO(bool $localized = false)
    {
        if (isset($this->modificationTimeStamp) and $this->modificationTimeStamp instanceof \DateTime) {
            return $this->getModificationTimeStamp($localized)->format(\DateTime::ISO8601);
        }

        return null;
    }

    /**
     * Setter for $timeZone.
     *
     * @param string $timeZone the time zone to set
     *
     * @return void
     */
    public function setTimeZone(string $timeZone)
    {
        $this->timeZone = $timeZone;
    }

    /**
     * Check if the Entity is deletable.
     *
     * By default, there is no checking, so this method does nothing.
     * It exists here because it will be called just before attempting to delete any Entity.
     * Entities that need checking should override this method and throw NotDeletableExceptions
     * when the Entity is not deletable.
     *
     * @see \Pelagos\Exception\NotDeletableException
     *
     *
     * @return void
     */
    #[ORM\PreRemove]
    public function checkDeletable()
    {
        // Do nothing.
    }

    /**
     * Static method to serialize a binary attribute.
     *
     * @param string $binaryData the binary data to serialize
     *
     * @return string the serialized binary data
     */
    public static function serializeBinary(string $binaryData)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($binaryData);

        return [
            'mimeType' => $mimeType,
            'base64' => base64_encode($binaryData),
        ];
    }

    /**
     * Return true if the class type and instance id of the $other are the same is $this.
     *
     * @param Entity $other the object to which this is compared
     *
     * @return bool return true if the type and id match
     */
    public function isSameTypeAndId(Entity $other)
    {
        if (
            get_class($this) == get_class($other) &&
            $this->getId() == $other->getId()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the entty is deletable.
     *
     * @return bool true is the entity is deletable, false otherwise
     */
    public function isDeletable()
    {
        try {
            $this->checkDeletable();
        } catch (NotDeletableException $e) {
            return false;
        }

        return true;
    }

    /**
     * Serializer for the creator virtual property.
     *
     *
     * @return array|null
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('creator')]
    public function serializeCreator()
    {
        if (!($this->creator instanceof Person)) {
            return null;
        }

        return [
            'id' => $this->creator->getId(),
            'firstName' => $this->creator->getFirstName(),
            'lastName' => $this->creator->getLastName(),
        ];
    }

    /**
     * Serializer for the modifier virtual property.
     *
     *
     * @return array|null
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('modifier')]
    public function serializeModifier()
    {
        if (!($this->modifier instanceof Person)) {
            return null;
        }

        return [
            'id' => $this->modifier->getId(),
            'firstName' => $this->modifier->getFirstName(),
            'lastName' => $this->modifier->getLastName(),
        ];
    }

    /**
     * Returns the name of this entity lowercased and separated by underscores.
     *
     * @return string
     */
    public function getUnderscoredName()
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', substr(strrchr(get_class($this), '\\'), 1)));
    }

    /**
     * Remove blanks from specified arrays.
     *
     * @param array $arrayWithBlanks an array that potentially contains blank line entries
     *
     * @return array the same array with blank lines removed, same order, but re-indexed
     */
    public function filterArrayBlanks(array $arrayWithBlanks)
    {
        // strlen callback
        return array_values(array_filter($arrayWithBlanks, function ($var) {
            $var = $var ?? '';
            return strlen($var);
        }));
    }
}
