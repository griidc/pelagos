<?php

namespace App\Event;

use App\Message\DoiMessage;

/**
 * Listener class for DOI deletion.
 */
class DoiDatasetListener extends EventListener
{
    /**
     * On dataset delete, delete DOI is called.
     *
     * @param EntityEvent $event A Doctrine entity.
     *
     * @return void
     */
    public function onDeleteDoi(EntityEvent $event)
    {
        $dataset = $event->getEntity();
        $doi = $dataset->getDoi();
        if ($doi) {
            $doiMessage = new DoiMessage((string) $doi->getDoi(), DoiMessage::DELETE_ACTION);
            $this->messageBus->dispatch($doiMessage);
        }
    }
}
