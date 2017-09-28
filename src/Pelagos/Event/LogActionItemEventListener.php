<?php
namespace Pelagos\Event;

use Pelagos\Entity\LogActionItem;

/**
 * Listener class for LogActionItem-related events.
 */
class LogActionItemEventListener extends EventListener
{
    /**
     * Method to persist a new LogAcionItem object to the database.
     *
     * @param LogActionItemEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onNewLogCreated(LogActionItemEvent $event)
    {
        $logActionItem = new LogActionItem(
            $event->getActionName(),
            $event->getSubjectEntityName(),
            $event->getSubjectEntityID(),
            $event->getPayLoad()
        );
        $this->entityHandler->create($logActionItem);
    }
}
