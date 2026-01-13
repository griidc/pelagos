<?php

namespace App\Event;

use App\Entity\File;
use App\Entity\InformationProduct;
use App\Message\InformationProductFiler;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Event\EntityLifecycleEventInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EasyAdminPersistSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public function onAfterEntityPersistedEvent(AfterEntityPersistedEvent $event): void
    {
        $this->sendMessage($event);
    }

    public function onAfterEntityUpdatedEvent(AfterEntityUpdatedEvent $event): void
    {
        $this->sendMessage($event);
    }

    private function sendMessage(EntityLifecycleEventInterface $event): void
    {
        $informationProduct = $event->getEntityInstance();

        if ($informationProduct instanceof InformationProduct && $informationProduct->getFile()?->getStatus() === File::FILE_NEW) {
            // Dispatch a message to process the information product after it has been persisted
            $this->messageBus->dispatch(new InformationProductFiler($informationProduct->getId()));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => 'onAfterEntityPersistedEvent',
            AfterEntityUpdatedEvent::class => 'onAfterEntityUpdatedEvent',
        ];
    }
}
