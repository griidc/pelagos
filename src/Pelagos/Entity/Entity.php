<?php
/**
 * This file contains an abstract implementation of a Pelagos entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage Entity
 */

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Abstract class that contains basic properties and methods common to all Pelagos entities.
 */
abstract class Entity implements \JsonSerializable
{
    /**
     * Entity identifier.
     *
     * @var int $id
     */
    protected $id;

    /**
     * The username of the user who created this Entity.
     *
     * @var string $creator;
     *
     * @Assert\NotBlank(
     *     message="Creator is required"
     * )
     */
    protected $creator;

    /**
     * The creation time stamp (in UTC) for this Entity.
     *
     * @var \DateTime $creationTimeStamp;
     */
    protected $creationTimeStamp;

    /**
     * The last modification time stamp (in UTC) for this Person.
     *
     * @var \DateTime $modificationTimeStamp;
     */
    protected $modificationTimeStamp;

    /**
     * The username of the user who last modified this Person.
     *
     * @var string $creator;
     *
     * @Assert\NotBlank(
     *     message="Modifier is required"
     * )
     */
    protected $modifier;

    /**
     * An array of properties to serialize.
     *
     * If not set, all serializable properties will be serialized.
     *
     * @var array $serializeProperties
     */
    protected $serializeProperties;

    /**
     * The time zone to use when returning time stamps.
     *
     * @var string $timeZone
     */
    protected $timeZone = 'UTC';

    /**
     * Static array containing a list of the properties and their attributes.
     *
     * @var array $properties
     */
    protected static $properties = array(
        'id' => array(
            'type' => 'integer',
            'updateable' => false,
            'getter' => 'getId',
        ),
        'creator' => array(
            'type' => 'string',
            'setter' => 'setCreator',
            'getter' => 'getCreator',
        ),
        'creationTimeStamp' => array(
            'type' => 'object',
            'class' => 'DateTime',
            'updateable' => false,
            'resolver' => 'resolveDateTime',
            'setter' => 'setCreationTimeStamp',
            'getter' => 'getCreationTimeStamp',
            'serializer' => 'serializeDateTime',
        ),
        'modifier' => array(
            'type' => 'string',
            'setter' => 'setModifier',
            'getter' => 'getModifier',
        ),
        'modificationTimeStamp' => array(
            'type' => 'object',
            'class' => 'DateTime',
            'updateable' => false,
            'resolver' => 'resolveDateTime',
            'setter' => 'setModificationTimeStamp',
            'getter' => 'getModificationTimeStamp',
            'serializer' => 'serializeDateTime',
        ),
    );

    /**
     * Return the combined properties of all classes in the inheritance tree.
     *
     * The Properties are static in all cases.
     * The properties are merged in the the argument to the function
     * and returned each iteration through the while.
     * The properties are accumulated bottom to top in the inheritance tree.
     *
     * @return array $props Set of properties
     */
    public static function getPropertiesWork()
    {
        $props = array();
        $className = get_called_class();
        $reflectionClass = new \ReflectionClass($className);
        $props = array_merge($className::$properties, $props); // static access to member properties

        while ($parentReflection = $reflectionClass->getParentClass()) {
            $parentClassName = $parentReflection->getName();
            $props = array_merge($parentClassName::$properties, $props); // static access to member properties
            $reflectionClass = $parentReflection;
        }
        return $props;
    }

    /**
     * Static method to check if a given property exists for this class.
     *
     * @param string $property The property to check.
     *
     * @return boolean Whether or not the given property exists.
     */
    public static function propertyExists($property)
    {
        $properties = static::getProperties();
        return array_key_exists($property, $properties);
    }

    /**
     * Static method to determine if a given property expects an entity.
     *
     * @param string $property The property to check.
     *
     * @return boolean Whether or not given property expects an entity.
     */
    public static function propertyExpectsEntity($property)
    {
        $properties = static::getProperties();
        if (array_key_exists($property, $properties)) {
            return array_key_exists('entity', $properties[$property]);
        }
        return false;
    }

    /**
     * Static method to get the expected entity type for a given property.
     *
     * @param string $property The property to get the entity type for.
     *
     * @return string The expected entity type for given property.
     */
    public static function getPropertyEntityType($property)
    {
        $properties = static::getProperties();
        if (array_key_exists($property, $properties)) {
            if (array_key_exists('entity', $properties[$property])) {
                return $properties[$property]['entity'];
            }
        }
        return null;
    }

    /**
     * Getter for id property.
     *
     * @return int Persistent identifier for the Entity.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setter for creator.
     *
     * @param string $creator This entity's creator's username.
     *
     * @access public
     *
     * @return void
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
        $this->modifier = $creator;
    }

    /**
     * Getter for creator.
     *
     * @access public
     *
     * @return string This entity's creator's username.
     */
    public function getCreator()
    {
        return $this->creator;
    }
    /**
     * Setter for modifier property.
     *
     * @param string $modifier The username of the user who modified this Person.
     *
     * @return void
     */
    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
    }

    /**
     * Getter for modifier property.
     *
     * @return string The username of the user who modified this Person.
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * Setter for creationTimeStamp property.
     *
     * @param \DateTime $timeStamp Creation time stamp to set.
     *
     * @return void
     *
     * @throws \Exception When $timeStamp does not have a timezone of UTC.
     */
    public function setCreationTimeStamp(\DateTime $timeStamp = null)
    {
        if (isset($timeStamp)) {
            if ($timeStamp->getTimezone()->getName() != 'UTC') {
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
     * @param boolean $localized Whether to convert time stamp to the local time zone.
     *
     * @return \DateTime Creation time stamp for this Person.
     */
    public function getCreationTimeStamp($localized = false)
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
     * @param boolean $localized Whether to convert time stamp to the local timezone.
     *
     * @return string ISO8601 string representing creationTimeStamp.
     */
    public function getCreationTimeStampAsISO($localized = false)
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
     * @return void
     */
    public function updateTimeStamps()
    {
        if ($this->creationTimeStamp == null) {
            $this->setCreationTimeStamp();
        }
        $this->setModificationTimeStamp();
    }

    /**
     * Setter for modificationTimeStamp property.
     *
     * @param \DateTime $timeStamp Modification time stamp to set.
     *
     * @return void
     *
     * @throws \Exception When $timeStamp does not have a timezone of UTC.
     */
    public function setModificationTimeStamp(\DateTime $timeStamp = null)
    {
        if (isset($timeStamp)) {
            if ($timeStamp->getTimezone()->getName() != 'UTC') {
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
     * @param boolean $localized Whether to convert time stamp to the local time zone.
     *
     * @return \DateTime Modification time stamp for this Person.
     */
    public function getModificationTimeStamp($localized = false)
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
     * @param boolean $localized Whether to convert time stamp to the local timezone.
     *
     * @return string ISO8601 string representing modificationTimeStamp.
     */
    public function getModificationTimeStampAsISO($localized = false)
    {
        if (isset($this->modificationTimeStamp) and $this->modificationTimeStamp instanceof \DateTime) {
            return $this->getModificationTimeStamp($localized)->format(\DateTime::ISO8601);
        }
        return null;
    }

    /**
     * Method to update multiple properties.
     *
     * @param array $updates An associative array indexed with property names
     *                       and containing each property's new value.
     *
     * @return Entity Return the updated object.
     */
    public function update(array $updates)
    {
        $properties = $this->getProperties();
        foreach ($updates as $field => $value) {
            // If this field is a valid property
            if (array_key_exists($field, $properties)) {
                // Skip this property if it has been marked as updateable = false
                if (array_key_exists('updateable', $properties[$field]) and !$properties[$field]['updateable']) {
                    continue;
                }
                // If a resolver has been defined for this property, use it to resolve the final value.
                if (array_key_exists('resolver', $properties[$field])) {
                    $value = $this->$properties[$field]['resolver']($value);
                }
                // If a setter has been defined, use it to set the vale of this property.
                if (array_key_exists('setter', $properties[$field])) {
                    $this->$properties[$field]['setter']($value);
                }
            }
        }
        return $this;
    }

    /**
     * Specify a list of properties to serialize.
     *
     * @param array $properties The list of properties to serialize.
     *
     * @return void
     */
    public function setSerializeProperties(array $properties)
    {
        $this->serializeProperties = $properties;
    }

    /**
     * Implement JsonSerializable.
     *
     * @return array An array suitable for JSON serialization of the object.
     */
    public function jsonSerialize()
    {
        $jsonArray = array();
        $serializeProperties = array();
        $properties = $this->getProperties();
        if (isset($this->serializeProperties)) {
            // If we've set which properties to serialize.
            foreach ($this->serializeProperties as $property) {
                if (array_key_exists($property, $properties)) {
                    // Grab property metadata for the property and add it to list to serialize.
                    $serializeProperties[$property] = $properties[$property];
                }
            }
        } else {
            // Otherwise, just serialize them all.
            $serializeProperties = $properties;
        }
        foreach ($serializeProperties as $property => $attributes) {
            // Skip this property if serialize = false
            if (array_key_exists('serialize', $attributes) and !$attributes['serialize']) {
                continue;
            }
            // If a getter has been defined, use it get this value
            if (array_key_exists('getter', $attributes)) {
                $jsonArray[$property] = $this->$attributes['getter']();
                // If a serializer has been defined, use it to serialize this value
                if (array_key_exists('serializer', $attributes)) {
                    $jsonArray[$property] = $this->$attributes['serializer']($jsonArray[$property]);
                }
            }
        }
        return $jsonArray;
    }

    /**
     * Setter for $timeZone.
     *
     * @param string $timeZone The time zone to set.
     *
     * @return void
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;
    }

    /**
     * Static method to resolve a value as a DateTime object.
     *
     * @param mixed $value A value to resolve to a DateTime.
     *
     * @return \DateTime The resolved DateTime.
     */
    public static function resolveDateTime($value)
    {
        if (!isset($value)) {
            return null;
        }
        if (gettype($value) == 'object' and get_class($value) == 'DateTime') {
            return $value;
        }
        return new \DateTime($value);
    }

    /**
     * Static method to serialize a DateTime object as an ISO8601 string.
     *
     * @param \DateTime|null $dateTime The DateTime to serialize.
     *
     * @return string The serialized DateTime.
     */
    public static function serializeDateTime($dateTime)
    {
        if (isset($dateTime) and $dateTime instanceof \DateTime) {
            return $dateTime->format(\DateTime::ISO8601);
        }
        return null;
    }

    /**
     * Static method to serialize a DateTime object as an ISO8601 string without time.
     *
     * @param \DateTime|null $dateTime The DateTime to serialize.
     *
     * @return string The serialized DateTime.
     */
    public static function serializeDate($dateTime)
    {
        if (isset($dateTime) and $dateTime instanceof \DateTime) {
            return $dateTime->format('Y-m-d');
        }
        return null;
    }

    /**
     * Static method to serialize a binary attribute.
     *
     * @param string $binaryData The binary data to serialize.
     *
     * @return string The serialized binary data.
     */
    public static function serializeBinary($binaryData)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($binaryData);

        return array(
            'mimeType' => $mimeType,
            'base64' => base64_encode($binaryData)
        );
    }
}
