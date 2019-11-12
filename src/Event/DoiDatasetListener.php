<?php

namespace App\Event;

use App\Util\RabbitPublisher;

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
            $this->publisher->publish((string) $doi->getDoi(), RabbitPublisher::DOI_PRODUCER, 'delete');
        }
    }
}
