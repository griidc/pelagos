<?php
namespace App\Event;

use App\Entity\LogActionItem;
use App\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Listener class for LogActionItem-related events.
 */
class LogActionItemEventListener extends EventListener
{
    /**
     * The entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * This is the class constructor to handle dependency injections.
     *
     * @param EntityManagerInterface $entityManager The entity manager.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Method to persist a new LogActionItem object to the database.
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
