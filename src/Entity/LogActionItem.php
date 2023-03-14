<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity class to represent a Data Repository Role.
 *
 * @ORM\Entity(repositoryClass="App\Repository\LogActionItemRepository")
 */
class LogActionItem extends Entity
{
    /**
     * A friendly name for this type of entity.
    */
    const FRIENDLY_NAME = 'Log Action Item';

    /**
     * Name of the action for this log item.
     *
     * @var string
     *
     * @access protected
     *
     * @ORM\Column(type="citext")
     *
     * @Assert\NotBlank(
     *      message="Action Name is required"
     * )
     */
    protected $actionName;

    /**
     * Specify subject name if it is an entity (null if the subject is not an entity).
     *
     * @var string
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable= true)
     */
    protected $subjectEntityName;

    /**
     * The Id of the subject if it is an entity (null if the subject is not an entity).
     *
     * @var integer
     *
     * @access protected
     *
     * @ORM\Column(type="integer", nullable = true)
     */
    protected $subjectEntityId;

    /**
     * Additional information for this log action item.
     *
     * @var array
     *
     * @access protected
     *
     * @ORM\Column(type="json", nullable = true)
     */
    protected $payLoad = array();

    /**
     * Constructor for LogActionItem.
     *
     * @param string     $actionName        The action Name for this Log Item.
     * @param string     $subjectEntityName The subject entity name for this Log Item.
     * @param integer    $subjectEntityId   The subject entity Id for this Log Item.
     * @param array      $payLoad           The additional information for this Log Item.
     *
     * @throws \Exception When there is no Subject Entity ID for a not-null Subject Entity Name.
     */
    public function __construct(
        string $actionName,
        string $subjectEntityName = null,
        int $subjectEntityId = null,
        array $payLoad = null
    ) {
        if ($actionName !== null) {
            $this->setActionName($actionName);
        }
        if ($subjectEntityName !== null) {
            $this->setSubjectEntityName($subjectEntityName);
            if ($subjectEntityId !== null) {
                $this->setSubjectEntityId($subjectEntityId);
            } else {
                throw new \Exception('Subject Entity Id is required.');
            }
        }
        if ($payLoad !== null) {
            $this->setPayLoad($payLoad);
        }
    }

    /**
     * Getter for actionName.
     *
     * @access public
     *
     * @return string  Action Name of the log action item
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * Setter for actionName.
     *
     * @param string $actionName The action Name to attach to this Action Log Item.
     *
     * @access public
     *
     * @return void
     */
    public function setActionName(string $actionName)
    {
        $this->actionName = $actionName;
    }

    /**
     * Getter for subjectEntityName.
     *
     * @access public
     *
     * @return string subject entity name of the log action item
     */
    public function getSubjectEntityName()
    {
        return $this->subjectEntityName;
    }

    /**
     * Setter for subjectEntityName.
     *
     * @param string|null $subjectEntityName The Subject Entity Name to attach to this Action Log Item.
     *
     * @access public
     *
     * @return void
     */
    public function setSubjectEntityName(?string $subjectEntityName)
    {
        $this->subjectEntityName = $subjectEntityName;
    }

    /**
     * Getter for subjectEntityId.
     *
     * @access public
     *
     * @return integer subject entity Id of the log action item
     */
    public function getSubjectEntityId()
    {
        return $this->subjectEntityId;
    }

    /**
     * Setter for subjectEntityId.
     *
     * @param integer|null $subjectEntityId The Subject Entity ID to attach to this Action Log Item.
     *
     * @access public
     *
     * @return void
     */
    public function setSubjectEntityId(?int $subjectEntityId)
    {
        $this->subjectEntityId = $subjectEntityId;
    }

    /**
     * Setter for payLoad.
     *
     * @param array|null $payLoad The additional information to attach to this Action Log Item (json).
     *
     * @access public
     *
     * @return void
     */
    public function setPayLoad(?array $payLoad)
    {
        $this->payLoad = $payLoad;
    }

    /**
     * Getter for payLoad.
     *
     * @access public
     *
     * @return array additional information for the log action item (json)
     */
    public function getPayLoad()
    {
        return $this->payLoad;
    }
}
