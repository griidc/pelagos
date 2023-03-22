<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class for dataset download events.
 */
class LogActionItemEvent extends Event
{
    /**
     * Name of the Action.
     *
     * @var string
     */
    protected $actionName;

    /**
     * The name of this event's subject if it is an entity.
     *
     * @var string
     */
    protected $subjectEntityName;

    /**
     * The Entity Id if the subjectEntityName is not null.
     *
     * @var integer
     */
    protected $subjectEntityId;

    /**
     * The additional information for this event.
     *
     * @var array
     */
    protected $payLoad = array();

    /**
     * Constructor.
     *
     * @param string       $actionName        The name of the action this event is for.
     * @param string|null  $subjectEntityName The subject name it is an entity.
     * @param integer|null $subjectEntityId   The subject Id if subject entity name exists.
     * @param array        $payLoad           The additional information for the event.
     */
    public function __construct(string $actionName, ?string $subjectEntityName, ?int $subjectEntityId, array $payLoad)
    {
        $this->actionName = $actionName;
        $this->subjectEntityName = $subjectEntityName;
        $this->subjectEntityId = $subjectEntityId;
        $this->payLoad = $payLoad;
    }

    /**
     * Getter for actionName.
     *
     * @access public
     *
     * @return string Name of the action of the log action item event
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * Getter for subjectEntityName.
     *
     * @access public
     *
     * @return string subject entity name of the log action item event
     */
    public function getSubjectEntityName()
    {
        return $this->subjectEntityName;
    }

    /**
     * Getter for subjectEntityId.
     *
     * @access public
     *
     * @return integer subject entity Id of the log action item event
     */
    public function getSubjectEntityId()
    {
        return $this->subjectEntityId;
    }

    /**
     * Getter for payLoad.
     *
     * @access public
     *
     * @return array additional information for the log action item event(json)
     */
    public function getPayLoad()
    {
        return $this->payLoad;
    }
}
