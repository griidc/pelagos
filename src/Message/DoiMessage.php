<?php


namespace App\Message;

/**
 * Class DoiMessage
 * @package App\Message
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
     * @var integer
     */
    private $messageId;

    /**
     * Action that needs to be taken on the Doi Message.
     *
     * @var string
     */
    private $action;

    /**
     * DoiMessage constructor.
     *
     * @param integer $messageId Id of the dataset or doi identifier.
     * @param string  $action    Action that needs to be taken on the Doi Message.
     */
    public function __construct(int $messageId, string $action)
    {
        $this->messageId = $messageId;
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
     * @return int
     */
    public function getMessageId(): int
    {
        return $this->messageId;
    }
}
