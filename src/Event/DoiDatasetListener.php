<?php

namespace Pelagos\Event;

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
            $this->producer->publish($doi->getDoi(), 'delete');
        }
    }
}
