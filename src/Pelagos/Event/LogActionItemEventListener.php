<?php
namespace Pelagos\Event;

use Doctrine\ORM\EntityManager;

use Pelagos\Entity\LogActionItem;
use Pelagos\Entity\Person;

/**
 * Listener class for LogActionItem-related events.
 */
class LogActionItemEventListener extends EventListener
{
    /**
     * The entity manager.
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * This is the class constructor to handle dependency injections.
     *
     * @param EntityManager $entityManager The entity manager.
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
        $systemPerson = $this->entityManager->find(Person::class, 0);
        $logActionItem->setCreator($systemPerson);
        $this->entityManager->persist($logActionItem);
        $this->entityManager->flush();
    }
}
