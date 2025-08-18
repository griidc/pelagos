<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\HasLifecycleCallbacks]
trait EntityDateTimeTrait
{
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    protected ?\DateTime $creationTimeStamp;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    protected \DateTime $modificationTimeStamp;

    #[ORM\ManyToOne(targetEntity: Person::class)]
    protected ?Person $creator = null;

    #[ORM\ManyToOne(targetEntity: Person::class)]
    protected ?Person $modifier = null;

    /**
     * Setter for creator.
     */
    public function setCreator(Person $person): void
    {
        $this->creator = $person;
        $this->modifier = $person;
    }

    /**
     * Getter for creator.
     */
    public function getCreator(): Person
    {
        return $this->creator;
    }

    /**
     * Setter for modifier property.
     */
    public function setModifier(Person $person): void
    {
        $this->modifier = $person;
    }

    /**
     * Getter for modifier property.
     */
    public function getModifier(): Person
    {
        return $this->modifier;
    }

    /**
     * Setter for creationTimeStamp property.
     */
    public function setCreationTimeStamp(\DateTime $timeStamp = new \DateTime('now', new \DateTimeZone('UTC'))): void
    {
        $this->creationTimeStamp = $timeStamp;
        $this->setModificationTimeStamp(clone $this->getCreationTimeStamp());
    }

    /**
     * Getter for creationTimeStamp property.
     */
    public function getCreationTimeStamp(): ?\DateTime
    {
        return $this->creationTimeStamp;
    }

    /**
     * Update the time stamps to the current time.
     *
     * The creation time stamp is only updated if not already set.
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
     */
    public function setModificationTimeStamp(\DateTime $timeStamp = new \DateTime('now', new \DateTimeZone('UTC'))): void
    {
        $this->modificationTimeStamp = $timeStamp;
    }

    /**
     * Getter for modificationTimeStamp property.
     */
    public function getModificationTimeStamp(): ?\DateTime
    {
        return $this->modificationTimeStamp;
    }
}