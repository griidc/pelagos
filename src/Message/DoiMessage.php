<?php

namespace App\Message;

/**
 * Class for Doi message.
 */
class DoiMessage
{
    /**
     * Delete action to delete doi associated with the dataset.
     */
    const DELETE_ACTION = 'delete';

    /**
     * Issue/update doi metadata for the dataset.
     */
    const ISSUE_OR_UPDATE = 'issue_or_update';

    /**
     * Id of the dataset or doi identifier.
     *
     * @var string
     */
    private $contextId;

    /**
     * Action that needs to be taken on the Doi Message.
     *
     * @var string
     */
    private $action;

    /**
     * DoiMessage constructor.
     *
     * @param string $contextId Id of the dataset or doi identifier.
     * @param string $action    Action that needs to be taken on the Doi Message.
     */
    public function __construct(string $contextId, string $action)
    {
        $this->contextId = $contextId;
        $this->action = $action;
    }

    /**
     * Get the action for doi message.
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Get the id for doi message (can be dataset id or doi).
     *
     * @return string
     */
    public function getContextId(): string
    {
        return $this->contextId;
    }
}
